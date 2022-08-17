<?php


namespace App\Presenters;

use App\Forms\AlbumPhotoFormFactory;
use App\Model\Person\PersonsRepository;
use App\Photo\PhotoService;
use App\Model\Album\Album;
use App\Model\Album\AlbumsRepository;
use App\Model\AlbumPhoto\AlbumPhoto;
use App\Model\AlbumPhoto\AlbumPhotosRepository;
use Nette\Application\BadRequestException;
use Nette\DI\Attributes\Inject;
use Nette\Forms\Form;
use Nette\Http\FileUpload;
use Nette\Http\IResponse;

use Nextras\Orm\Exception\NoResultException;
use Tracy\Debugger;

final class AlbumPresenter extends BasePresenter
{
	#[Inject]
	public AlbumsRepository $albumsRepository;

	#[Inject]
	public AlbumPhotosRepository $photosRepository;

	#[Inject]
	public PhotoService $photoService;

	#[Inject]
	public PersonsRepository $personsRepository;

	#[Inject]
	public AlbumPhotoFormFactory $formFactory;

	private Album $album;

	public function actionView(string $slug, string $hash = null): void
	{
		$this->getAlbumBySlug($slug);

		$this->template->photos = $this->album->findPhotos();

		$this->template->mediumPath = $this->photoService->getRelativePhotoPath($this->album->id, PhotoService::PHOTO_TYPE_MEDIUM);
		$this->template->largePath = $this->photoService->getRelativePhotoPath($this->album->id, PhotoService::PHOTO_TYPE_LARGE);
		$this->template->originalPath = $this->photoService->getRelativePhotoPath($this->album->id);
	}

	public function actionUpload(string $slug): void
	{
		$this->getAlbumBySlug($slug);
	}

	public function actionEdit(string $slug): void
	{
		$this->getAlbumBySlug($slug);

		$this->template->originalPath = $this->photoService->getRelativePhotoPath($this->album->id);
	}

	public function actionDelete(int $id): void
	{
		$album = $this->getAlbumById($id);
		$this->albumsRepository->removeAndFlush($album);

		$this->flashMessage('Album bylo smazáno');
		$this->redirect('Homepage:albums');
	}

	public function actionVisibility(int $id, bool $public): void
	{
		$album = $this->getAlbumById($id);
		$album->public = $public;

		$this->albumsRepository->persistAndFlush($album);

		$this->flashMessage('Viditelnost alba byla změněna');
		$this->redirect('view', $album->slug);
	}

	protected function createComponentAlbumForm(): Form
	{
		$persons = $this->personsRepository->getPersonList();
		$form = $this->formFactory->createAlbumPhotoForm($this->album, $persons);

		$form->getComponent('save')->onClick[] = function () {
			$this->flashMessage('Album bylo upraveno');
			$this->redirect('view', $this->album->slug);
		};

		$form->getComponent('delete')->onClick[] = function () {
			$this->flashMessage('Fotografie byly smazány');
			$this->redirect('view', $this->album->slug);
		};

		$form->getComponent('visible')->onClick[] = function () {
			$this->flashMessage('Viditelnost fotografií byla změněna');
			$this->redirect('view', $this->album->slug);
		};

		return $form;
	}

	public function handlePhotoUpload(): void
	{
		if (!($file = $_FILES['file'] ?? null)) {
			throw new \ErrorException('File do not exists');
		}

		$fileUpload = new FileUpload($file);
		$httpResponse = $this->getHttpResponse();

		if ($fileUpload->hasFile()) {
			if (!$fileUpload->isImage()) {
				$httpResponse->setCode(IResponse::S422_UNPROCESSABLE_ENTITY);
				$this->sendJson(['error' => 'Vybraný soubor není obrázek']);
			} elseif ($fileUpload->isOk()) {
				$hash = md5_file($fileUpload->getTemporaryFile());

				if ($photo = $this->photosRepository->getByHash($this->album->id, $hash)) {
					$httpResponse->setCode(IResponse::S422_UNPROCESSABLE_ENTITY);
					$this->sendJson(['error' => 'Fotografie již v albu existuje']);
				} else {
					list($fileName, $thumbName) = $this->photoService->uploadPhoto($fileUpload, $this->album->id);

					//Pokud existuje datum vytvoření fotografie a jsou zde fotografie staršího data, změni jejich pořadí,
					// jinak je to poslední fotka a patří na konec
					$updateOrder = false;
					if (!($takenAt = $this->photoService->getPhotoDate($this->album->id, $fileName))) {
						$takenAt = new \DateTimeImmutable();
					} elseif ($last = $this->album->getLastPhotoByCreatedAt($takenAt)) {
						$updateOrder = true;
						$order = $last->order;
					}

					if (!$updateOrder) {
						$order = $this->album->getMaxPhotosOrder() + 1;
					}

					$photo = new AlbumPhoto();
					$photo->filename = $fileName;
					$photo->thumbname = $thumbName;
					$photo->hash = $hash;
					$photo->createdBy = $this->personsRepository->getByIdChecked($this->user);
					$photo->album = $this->album;
					$photo->takenAt = $takenAt;
					$photo->order = $order;
					$this->photosRepository->persistAndFlush($photo);

					if ($updateOrder) {
						$this->album->updatePhotosOrder($takenAt);
					}

					$this->sendJson(['success' => 'Nahrán soubor: ' . $fileName]);
				}
			}
		}
	}

	private function getAlbumBySlug(string $slug): void
	{
		try {
			$this->album = $this->albumsRepository->getBySlug($slug);
		} catch (NoResultException $exception) {
			throw new BadRequestException('Album do not exists');
		}

		$this->template->album = $this->album;
		$this->template->thumbPath = $this->photoService->getRelativePhotoPath($this->album->id, PhotoService::PHOTO_TYPE_SMALL);
	}

	private function getAlbumById(int $id): Album
	{
		try {
			$album = $this->albumsRepository->getByIdChecked($id);
		} catch (NoResultException $exception) {
			throw new BadRequestException('Album do not exists');
		}

		return $album;
	}

}
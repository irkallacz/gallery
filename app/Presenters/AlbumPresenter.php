<?php


namespace App\Presenters;

use App\Forms\AlbumPhotoFormFactory;
use App\Model\Person\PersonsRepository;
use App\Image\ImageService;
use App\Model\Album\Album;
use App\Model\Album\AlbumsRepository;
use App\Model\AlbumPhoto\AlbumPhoto;
use App\Model\AlbumPhoto\AlbumPhotosRepository;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\DI\Attributes\Inject;
use Nette\Forms\Form;
use Nette\Http\FileUpload;
use Nette\Http\IResponse;

use Nette\Security\AuthenticationException;
use Nextras\Orm\Entity\AbstractEntity;
use Nextras\Orm\Exception\NoResultException;
use Tracy\Debugger;
use function Symfony\Component\String\b;

final class AlbumPresenter extends BasePresenter
{
	#[Inject]
	public AlbumsRepository $albumsRepository;

	#[Inject]
	public AlbumPhotosRepository $photosRepository;

	#[Inject]
	public ImageService $imageService;

	#[Inject]
	public PersonsRepository $personsRepository;

	#[Inject]
	public AlbumPhotoFormFactory $formFactory;

	private Album $album;

	public function actionView(string $slug, string $hash = null): void
	{
		try {
			$this->album = $this->albumsRepository->getBySlug($slug);
		} catch (NoResultException $exception) {
			throw new BadRequestException('Album do not exists');
		}

		$publicOnly = !$this->user->isLoggedIn();

		if ($hash !== $this->album->hash) {
			if ((!$this->album->public) && (!$this->user->isLoggedIn())) {
				$backlink = $this->storeRequest();
				$this->redirect('Sign:in', ['backlink' => $backlink]);
			}

			if (!$this->user->authorizator->isAllowed($this->user->identity ?? 'guest', $this->album, 'view')) {
				throw new ForbiddenRequestException('You dont have rights for this action');
			}
		} else {
			$publicOnly = false;
		}

		$this->template->publicOnly = $publicOnly;
		$this->template->photos = $this->album->findPhotos($publicOnly);
	}

	public function renderView(string $slug, string $hash = null)
	{
		$this->template->album = $this->album;
		$this->template->thumbPath = $this->imageService->getRelativeImagePath($this->album->id, ImageService::IMAGE_TYPE_SMALL);
		$this->template->mediumPath = $this->imageService->getRelativeImagePath($this->album->id, ImageService::IMAGE_TYPE_MEDIUM);
		$this->template->largePath = $this->imageService->getRelativeImagePath($this->album->id, ImageService::IMAGE_TYPE_LARGE);
		$this->template->originalPath = $this->imageService->getRelativeImagePath($this->album->id);
	}

	public function actionUpload(string $slug): void
	{
		$this->getAlbumBySlug($slug, 'upload');
	}

	public function actionEdit(string $slug): void
	{
		$this->getAlbumBySlug($slug, 'edit');
	}

	public function renderEdit(string $slug): void
	{
		$this->template->originalPath = $this->imageService->getRelativeImagePath($this->album->id);
	}

	public function actionDelete(int $id): void
	{
		$album = $this->getAlbumById($id, 'delete');
		$this->albumsRepository->removeAndFlush($album);

		$this->flashMessage('Album bylo smazáno');
		$this->redirect('Homepage:albums');
	}

	public function actionVisibility(int $id, bool $public): void
	{
		$album = $this->getAlbumById($id, 'visibility');
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
		$httpRequest = $this->getHttpRequest();

		if ($fileUpload = $httpRequest->getFile('file')) {
			if ($fileUpload->hasFile()) {
				$httpResponse = $this->getHttpResponse();

				if (!$fileUpload->isOk()) {
					$httpResponse->setCode(IResponse::S422_UNPROCESSABLE_ENTITY);
					$this->sendJson(['error' => 'Chyba při náhrávání souboru']);
				}

				if (!$this->imageService->isImage($fileUpload)) {
					$httpResponse->setCode(IResponse::S422_UNPROCESSABLE_ENTITY);
					$this->sendJson(['error' => 'Vybraný soubor není obrázek']);
				}

				$hash = md5_file($fileUpload->getTemporaryFile());

				if ($photo = $this->photosRepository->getByHash($this->album->id, $hash)) {
					$httpResponse->setCode(IResponse::S422_UNPROCESSABLE_ENTITY);
					$this->sendJson(['error' => 'Fotografie již v albu existuje']);
				}

				list($fileName, $thumbName) = $this->imageService->uploadImage($fileUpload, $this->album->id);

				//Pokud existuje datum vytvoření fotografie a jsou zde fotografie staršího data, změni jejich pořadí,
				// jinak je to poslední fotka a patří na konec
				$updateOrder = false;
				if (!($takenAt = $this->imageService->getImageDate($this->album->id, $fileName))) {
					$takenAt = new \DateTimeImmutable();
				} elseif ($last = $this->album->getLastPhotoByTakenAt($takenAt)) {
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
				$photo->createdBy = $this->personsRepository->getByIdChecked($this->user->id);
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

	private function getAlbumBySlug(string $slug, string $action): void
	{
		try {
			$this->album = $this->albumsRepository->getBySlug($slug);
		} catch (NoResultException $exception) {
			throw new BadRequestException('Album do not exists');
		}

		$this->template->album = $this->album;

		if (!$this->user->isLoggedIn()) {
			$backlink = $this->storeRequest();
			$this->redirect('Sign:in', ['backlink' => $backlink]);
		}

		if (!$this->user->authorizator->isAllowed($this->user->identity ?? 'guest', $this->album, $action)) {
			throw new ForbiddenRequestException('You dont have rights for this action');
		}
	}

	private function getAlbumById(int $id, string $action): Album
	{
		if (!$this->user->isLoggedIn()) {
			throw new ForbiddenRequestException('You need to be log in');
		}

		try {
			$album = $this->albumsRepository->getByIdChecked($id);
		} catch (NoResultException $exception) {
			throw new BadRequestException('Album do not exists');
		}

		if (!$this->user->authorizator->isAllowed($this->user->identity, $album, $action)) {
			throw new ForbiddenRequestException('You dont have rights for this action');
		}

		return $album;
	}

}
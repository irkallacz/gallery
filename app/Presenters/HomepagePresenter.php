<?php declare(strict_types=1);

namespace App\Presenters;

use App\Forms\AlbumFormFactory;
use App\Model\Album\Album;
use App\Model\Person\PersonsRepository;
use App\Model\Album\AlbumsRepository;
use App\Model\AlbumPhoto\AlbumPhotosRepository;
use Nette\Application\UI\Form;
use Nette\DI\Attributes\Inject;
use Nette\Utils\Strings;
use Nextras\Orm\Collection\ICollection;
use Tracy\Debugger;

final class HomepagePresenter extends BasePresenter
{
	#[Inject]
	public AlbumsRepository $albumsRepository;

	#[Inject]
	public AlbumPhotosRepository $photosRepository;

	#[Inject]
	public PersonsRepository $personsRepository;

	const ALBUM_COUNT = 30;

	public int $offset = 0;

	public function renderDefault(): void
	{
		$this->template->photos = $this->photosRepository->findRandomPhotos()
			->limitBy(10);

		$this->template->albums = $this->albumsRepository->findByVisibility()
			->orderBy('createdAt', ICollection::DESC)
			->limitBy(5);

		if ($this->user->isLoggedIn()) {
			$dateLast = new \DateTimeImmutable('2022-08-01');

			$this->template->newAlbums = $this->albumsRepository->findBy(['modifiedAt>' => $dateLast])
				->orderBy('modifiedAt', ICollection::DESC);

			$this->template->newAlbumPhotos = $this->albumsRepository->findBy(['photos->createdAt>' => $dateLast])
				->orderBy('photos->createdAt', ICollection::DESC);

			$this->template->dateLast = $dateLast;
			$this->template->albumCount = $this->albumsRepository->findBy(['createdBy' => $this->user->id])->count();
			$this->template->photoCount = $this->photosRepository->findBy(['createdBy' => $this->user->id])->count();
		}
	}

	public function renderAlbums(): void
	{
		$albums = $this->albumsRepository->findByVisibility()
			->orderBy('date', ICollection::DESC)
			->limitBy(self::ALBUM_COUNT + 1, $this->offset)
			->fetchAll();

		if (count($albums) > self::ALBUM_COUNT) {
			$this->template->offset = $this->offset + self::ALBUM_COUNT;
			array_pop($albums);
		} else {
			$this->template->offset = 0;
		}

		$this->template->albums = $albums;
	}

	public function handleLoadMore(int $offset): void
	{
		$this->offset = $offset;

		if ($this->presenter->isAjax()) {
			$this->redrawControl('albums');
			$this->redrawControl('loadMore');
		}
	}

	protected function createComponentAlbumForm(): Form
	{
		$form = (new AlbumFormFactory())
			->createForm();

		$form->addSubmit('save', 'uložit')
			->onClick[] = function (Form $form, Album $album) {
				$album->slug = Strings::webalize($album->title);
				$album->createdBy = $this->personsRepository->getByIdChecked($this->user);

				$this->albumsRepository->persistAndFlush($album);

				$this->flashMessage('Album bylo vytvořeno');
				$this->redirect('Album:upload', $album->slug);
			};

		$form->addSubmit('close', 'zavřít')
			->setHtmlId('close-album-form-button')
			->setHtmlAttribute('type', 'reset');

		return $form;
	}

}
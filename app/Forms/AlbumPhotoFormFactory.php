<?php


namespace App\Forms;

use App\Model\Album\Album;
use App\Model\Album\AlbumsRepository;
use App\Model\AlbumPhoto\AlbumPhoto;
use App\Model\AlbumPhoto\AlbumPhotosRepository;
use Nette\Application\UI\Form;
use Nette\Forms\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\ArrayHash;
use Nextras\Orm\Entity\ToArrayConverter;
use Nextras\Orm\Exception\NoResultException;
use Tracy\Debugger;

final class AlbumPhotoFormFactory extends AlbumFormFactory
{
	private AlbumsRepository $albumsRepository;

	private AlbumPhotosRepository $photosRepository;

	private Album $album;

	private array $persons;

	/**
	 * AlbumFormFactory constructor.
	 * @param AlbumsRepository $albumsRepository
	 * @param AlbumPhotosRepository $photosRepository
	 */
	public function __construct(AlbumsRepository $albumsRepository, AlbumPhotosRepository $photosRepository)
	{
		$this->albumsRepository = $albumsRepository;
		$this->photosRepository = $photosRepository;
	}

	public function createAlbumPhotoForm(Album $album, array $persons): Form
	{
		$this->album = $album;
		$this->persons = $persons;

		return $this->createForm();
	}

	public function createForm(): Form
	{
		$form = parent::createForm();

		$form->addText('slug', 'Slug', 50, 50)
			->setRequired('Vyplňte %label');

		$form->addSelect('createdBy', 'Uživatel', $this->persons);

		$form->addCheckBox('selectAll', 'Vybrat vše')
			->setDefaultValue(false)
			->setHtmlId('select-all')
			->setOmitted();

		$form->addMultiplier('photos', function (Container $photo) {
			$photo->addText('summary', 'Popis', 30, 50)
				->setNullable();
			$photo->addCheckBox('selected')
				->setHtmlAttribute('class', 'select')
				->setDefaultValue(false);
		}, 0);

		$form->getComponent('slug')->addRule(function (BaseControl $item) {
			try {
				$album = $this->albumsRepository->getBySlug($item->getValue());
			} catch (NoResultException $exception) {
				return true;
			}

			return ($album->id == $this->album->id);
		}, 'Url Alba musí být unikátní');

		$form->addSubmit('save', 'uložit změny')
			->onClick[] = [$this, 'save'];

		$form->addSubmit('delete', 'vymazat vybrané')
			->setHtmlAttribute('class', 'confirm')
			->setHtmlAttribute('data-confirm', 'Opravdu chcete smazat tyto fotografie?')
			->onClick[] = [$this, 'delete'];

		$form->addSubmit('visible', 'změnit viditelnost')
			->onClick[] = [$this, 'visible'];

		$form->setDefaults($this->album->toArray(ToArrayConverter::RELATIONSHIP_AS_ID));
		$form->setDefaults(['photos' => $this->album->photos->toCollection()->fetchPairs('id')]);

		return $form;
	}

	public function save(Form $form, ArrayHash $album): void
	{
		if ($this->album->title != $album->title) $this->album->title = $album->title;
		if ($this->album->slug != $album->slug) $this->album->slug = $album->slug;
		if ($this->album->createdBy->id != $album->createdBy) $this->album->createdBy = $album->createdBy;
		if ($this->album->date != $album->date) $this->album->date = $album->date;
		if ($this->album->summary != $album->summary) $this->album->summary = $album->summary;
		if ($this->album->description != $album->description) $this->album->description = $album->description;

		if ($this->album->isModified()) {
			$this->album->modifiedAt = new \DateTimeImmutable();
			$this->albumsRepository->persistAndFlush($this->album);
		}

		$photos = $this->album->photos->toCollection()->fetchPairs('id');

		$order = 0;
		foreach ($album->photos as $id => $values) {
			/**@var AlbumPhoto $photo */
			$photo = $photos[$id];
			$update = false;

			if ($photo->summary != $values->summary) {
				$photo->summary = $values->summary;
				$update = true;
			}

			if ($photo->order != $order) {
				$photo->order = $order;
				$update = true;
			}

			if ($update) {
				$this->photosRepository->persist($photo);
			}
			$order++;
		}

		$this->photosRepository->flush();
	}

	public function delete(Form $form): void
	{
		foreach ($this::getSelectedPhotos($form) as $id) {
			$photo = $this->photosRepository->getByIdChecked($id);
			$this->photosRepository->remove($photo);
		}

		$this->photosRepository->flush();
	}

	public function visible(Form $form): void
	{
		foreach ($this::getSelectedPhotos($form) as $id) {
			$photo = $this->photosRepository->getByIdChecked($id);
			$photo->public = !$photo->public;
			$this->photosRepository->persist($photo);
		}

		$this->photosRepository->flush();
	}

	private static function getSelectedPhotos(Form $form): array
	{
		return array_keys(array_filter($form->getValues('array')['photos'], function ($value) {
			return $value['selected'] == true;
		}));
	}
}
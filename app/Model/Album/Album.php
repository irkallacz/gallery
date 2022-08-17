<?php declare(strict_types = 1);

namespace App\Model\Album;

use App\Model\Person\Person;
use App\Model\AlbumPhoto\AlbumPhoto;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Entity\Entity;
use DateTimeImmutable;
use Nextras\Orm\Relationships\OneHasMany;

/**
 * Album Entity class
 * @property int 						$id {primary}
 * @property string						$title
 * @property string						$slug
 * @property string|null				$summary
 * @property string|null				$description
 * @property bool						$public	{default false}
 * @property string						$hash
 * @property DateTimeImmutable			$date
 * @property OneHasMany|AlbumPhoto[]	$photos {1:m AlbumPhoto::$album, orderBy=[order=ASC]}
 * @property DateTimeImmutable			$createdAt {default now}
 * @property DateTimeImmutable			$modifiedAt {default now}
 * @property Person|null				$createdBy {m:1 Person, oneSided=true}
 */
final class Album extends Entity
{
	public function findPhotos(bool $public = true): ICollection
	{
		$photos = $this->photos->toCollection();
		if (!$public) {
			$photos->findBy(['public' => true]);
		}

		return $photos;
	}

	public function updatePhotosOrder(\DateTimeInterface $greaterThen): void
	{
		$this->getRepository()->resetPhotosOrder($this->id, $greaterThen);
	}

	public function getLastPhotoByCreatedAt(\DateTimeInterface $greaterThen): AlbumPhoto
	{
		return $this->photos->toCollection()
			->findBy(['takenAt>' => $greaterThen])
			->orderBy('takenAt')
			->limitBy(1)
			->fetch();
	}

	public function getMaxPhotosOrder(): int
	{
		return $this->getRepository()->getMaxPhotosOrder($this->id);
	}

	public function onBeforeInsert(): void
	{
		parent::onBeforeInsert();
		$this->hash = md5(uniqid());
	}

	public function onAfterInsert(): void
	{
		parent::onAfterInsert();
		$this->slug = $this->id . '-' . $this->slug;

		//$this->getRepository()->persistAndFlush($this);
		$this->getRepository()->flush();
	}
}

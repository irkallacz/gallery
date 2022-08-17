<?php declare(strict_types = 1);

namespace App\Model\Album;

use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Repository\Repository;

/**
 * @method void resetPhotosOrder(int $album_id, \DateTimeInterface $greater_then)
 * @method void getMaxPhotosOrder(int $album_id)
 */
final class AlbumsRepository extends Repository
{
	public static function getEntityClassNames(): array
	{
		return [Album::class];
	}

	public function getBySlug(string $slug): Album
	{
		return $this->getByChecked(['slug' => $slug]);
	}

	public function findByVisibility(bool $publicOnly = false): ICollection
	{
		return ($publicOnly) ? $this->findBy(['public' => true]) : $this->findAll();
	}
}

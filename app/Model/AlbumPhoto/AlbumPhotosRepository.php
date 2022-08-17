<?php 
namespace App\Model\AlbumPhoto;

use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Repository\Repository;

/**
 * @method ICollection|AlbumPhoto[] findRandomPhotos()
 */

final class AlbumPhotosRepository extends Repository
{
	public static function getEntityClassNames(): array
	{
		return [AlbumPhoto::class];
	}

	public function getByHash(int $albumId, string $hash): ?AlbumPhoto
	{
		return $this->getBy(['album' => $albumId, 'hash' => $hash]);
	}
}

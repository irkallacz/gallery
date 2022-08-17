<?php declare(strict_types = 1);

namespace App\Model\Album;

use Nextras\Orm\Mapper\Mapper;

final class AlbumsMapper extends Mapper
{
	public function getMaxPhotosOrder(int $album_id): int
	{
		return $this->connection->query('SELECT MAX(`order`) FROM `album_photos` WHERE `album_id` = %i', $album_id)->fetchField();
	}

	public function resetPhotosOrder(int $album_id, \DateTimeInterface $greater_then): void
	{
		$this->connection->query('UPDATE `album_photos` SET `order` = `order` + 1 WHERE `album_id` = %i AND taken_at > %dt', $album_id, $greater_then);
	}

}

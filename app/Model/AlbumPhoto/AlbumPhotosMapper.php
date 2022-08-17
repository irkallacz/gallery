<?php 
namespace App\Model\AlbumPhoto;

use Nextras\Orm\Mapper\Mapper;
use Nextras\Orm\Collection\ICollection;

final class AlbumPhotosMapper extends Mapper
{
	public function findRandomPhotos(): ICollection
	{
		return $this->toCollection(
			$this->builder()->addOrderBy('RAND()')
		);
	}

}

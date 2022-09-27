<?php


namespace App\Model;

use App\Model\Album\AlbumsRepository;
use App\Model\AlbumPhoto\AlbumPhotosRepository;
use App\Model\Log\LogsRepository;
use App\Model\Person\PersonsRepository;
use App\Model\Right\RightsRepository;
use Nextras\Orm\Model\Model;

/**
 * @property-read AlbumsRepository			$albumsRepository
 * @property-read AlbumPhotosRepository		$albumPhotosRepository
 * @property-read PersonsRepository         $personsRepository
 * @property-read RightsRepository         	$rightsRepository
 * @property-read LogsRepository         	$logsRepository
 */
final class Orm extends Model
{
}
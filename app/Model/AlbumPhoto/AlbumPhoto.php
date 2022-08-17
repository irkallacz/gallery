<?php 
namespace App\Model\AlbumPhoto;

use App\Model\Album\Album;
use App\Model\Person\Person;
use Nextras\Orm\Entity\Entity;
use DateTimeImmutable;

/**
 * AlbumPhoto Entity class
 * @property int 						$id {primary}
 * @property string						$filename
 * @property string						$thumbname
 * @property string|null				$summary
 * @property int|null					$order
 * @property bool						$public	{default false}
 * @property Album						$album {m:1 Album::$photos}
 * @property string						$hash
 * @property DateTimeImmutable			$createdAt {default now}
 * @property DateTimeImmutable			$modifiedAt {default now}
 * @property DateTimeImmutable|null		$takenAt
 * @property Person|null				$createdBy {m:1 Person, oneSided=true}
 */

final class AlbumPhoto extends Entity
{
}

<?php


namespace App\Model\Log;


use App\Model\Person\Person;
use Nextras\Orm\Entity\Entity;

/**
 * @property int						$id {primary}
 * @property string 					$resource
 * @property string 					$action
 * @property string|null 				$param
 * @property Person|null				$createdBy {m:1 Person::$logs}
 * @property \DateTimeImmutable			$createdAt {default now}
 */

final class Log extends Entity
{

}
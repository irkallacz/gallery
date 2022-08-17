<?php


namespace App\Model\Person;

use Nextras\Orm\Entity\Entity;
use DateTimeImmutable;
use Nextras\Orm\Relationships\OneHasMany;
use Tracy\Debugger;

/**
 * @property int						$id {primary}
 * @property string 					$name
 * @property string 					$surname
 * @property-read string				$fullName {virtual}
 * @property int|null 					$role {enum self::ROLE_*} {default self::ROLE_MEMBER}
 * @property DateTimeImmutable			$createdAt
 * @property DateTimeImmutable			$modifiedAt
 */

final class Person extends Entity
{
	public const ROLE_USER = 0;
	public const ROLE_MEMBER = 1;
	public const ROLE_SUPERVISOR = 2;
	public const ROLE_ADMIN = 3;

	protected function getterFullName(): string
	{
		return $this->surname . ' ' . $this->name;
	}

}
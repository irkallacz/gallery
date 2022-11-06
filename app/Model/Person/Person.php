<?php


namespace App\Model\Person;

use App\Model\Log\Log;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Entity\Entity;
use DateTimeImmutable;
use Nextras\Orm\Relationships\OneHasMany;
use Tracy\Debugger;

/**
 * @property int						$id {primary}
 * @property string 					$name
 * @property string 					$surname
 * @property string 					$mail
 * @property string 					$mail2
 * @property-read string				$fullName {virtual}
 * @property-read  string|null 			$role
 * @property-read array|null 			$rights {wrapper \App\Model\SetsWrapper}
 * @property OneHasMany|Log[]			$logs {1:m Log::$createdBy}
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

	public function getRoles(): array
	{
		$roles = [];

		if (in_array('gallery', $this->rights)) {
			$roles[] = 'gallery';
		}

		if (in_array($this->role, ['member', 'editor', 'admin'])) {
			$roles[] = 'member';
		} else {
			$roles[] = 'user';
		}

		return $roles;
	}

	public function getLastLogin(): DateTimeImmutable
	{
		$log = $this->logs->toCollection()
			->findBy(['resource' => 'gallery', 'action' => 'log_in'])
			->orderBy(['createdAt' => ICollection::DESC])
			->fetch();

		if ($log) {
			return $log->createdAt;
		} else {
			return new DateTimeImmutable();
		}
	}

	public function addNewLogin()
	{
		$log = new Log();
		$log->resource = 'gallery';
		$log->action = 'log_in';

		$this->logs->add($log);

		$this->getRepository()->persistAndFlush($this);

	}


}

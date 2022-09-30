<?php


namespace App\Model\Person;


use Nextras\Orm\Repository\Repository;
use Nextras\Orm\Collection\ICollection;

final class PersonsRepository extends Repository
{
	public static function getEntityClassNames(): array
	{
		return [Person::class];
	}

	public function findByRole(int $role = Person::ROLE_MEMBER): ICollection | array
	{
		return $this->findBy(['role>' => $role]);
	}

	public function getPersonList(int $role = Person::ROLE_USER): array
	{
		return $this->findByRole($role)
			->orderBy(['surname' => ICollection::ASC, 'name' => ICollection::ASC])
			->fetchPairs('id', 'fullName');
	}
}
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

	public function findByRole(int $role = Person::ROLE_MEMBER): ICollection|array
	{
		return $this->findBy(['role>=' => $role]);
	}

	public function getPersonList(int $role = Person::ROLE_USER): array
	{
		return $this->findByRole($role)
			->orderBy(['surname' => ICollection::ASC, 'name' => ICollection::ASC])
			->fetchPairs('id', 'fullName');
	}

	public function getByMail(string $mail): ?Person
	{
		return $this->getBy([ICollection::OR, 'mail' => $mail, 'mail2' => $mail]);
	}

	public function getByCredentials(string $name, string $surname, \DateTimeImmutable $dateBorn): ?Person
	{
		return $this->getBy(['name' => $name, 'surname' => $surname, 'dateBorn' => $dateBorn]);
	}

	public function isEmailUnique(string $mail, ?int $person): bool
	{
		$conditions = [ICollection::OR, 'mail' => $mail, 'mail2' => $mail];

		if ($person) {
			$conditions = [ICollection::AND, ['id!=' => $person], $conditions];
		}

		return !(bool) $this->getBy($conditions);
	}
}
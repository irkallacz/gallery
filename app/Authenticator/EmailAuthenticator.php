<?php


namespace App\Authenticator;


use App\Model\Person\PersonsRepository;
use Nette\Security\AuthenticationException;
use Nette\Security\SimpleIdentity;
use Nette\Security\User;

final class EmailAuthenticator
{
	private PersonsRepository $personsRepository;
	private User $user;

	/**
	 * EmailAuthenticator constructor.
	 * @param PersonsRepository $personsRepository
	 * @param User $user
	 */
	public function __construct(PersonsRepository $personsRepository, User $user)
	{
		$this->personsRepository = $personsRepository;
		$this->user = $user;
	}

	public function authenticate(string $email): void
	{
		$person = $this->personsRepository->getBy(['mail' => $email]);

		if (!$person) {
			throw new AuthenticationException('User not found.');
		}

		$roles = ['user'];
		if ($person->rights) {
			$roles = array_merge($roles, explode(',', $person->rights));
		}

		$this->user->login(new SimpleIdentity($person->id, $roles,
			[
				'name' => $person->name,
				'surname' => $person->surname,
				'mail' => $person->mail,
			]
		));
	}
}
<?php


namespace App\Auth;


use App\Model\Person\PersonsRepository;
use Nette\Security\AuthenticationException;
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
		$person = $this->personsRepository->getByMail($email);

		if (!$person) {
			throw new AuthenticationException('User not found.');
		}

		$this->user->login(new AuthUser($person->id, $person->getRoles(),
			[
				'name' => $person->name,
				'surname' => $person->surname,
				'mail' => $person->mail,
				'lastLogin' => $person->getLastLogin()
			]
		));

		$person->addNewLogin();
	}
}
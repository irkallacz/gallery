<?php


namespace App\Auth;


use Nette\Security\IIdentity;
use Nette\Security\Role;
use DateTimeImmutable;

final class AuthUser implements IIdentity, Role
{
	private int $id;

	/**
	 * @var string[]
	 */
	private array $roles;

	public string $name;

	public string $surname;

	public string $mail;

	public DateTimeImmutable $lastLogin;

	public ?string $token = null;

	public ?int $tokenExpiration = null;

	/**
	 * AuthUser constructor.
	 * @param int $id
	 * @param string[] $roles
	 * @param array $data
	 */
	public function __construct(int $id, array $roles, array $data = [])
	{
		$this->id = $id;
		$this->roles = $roles;

		foreach ($data as $property => $value) {
			if (property_exists($this, $property)) {
				$this->{$property} = $value;
			}
		}
	}

	public function getId(): int
	{
		return $this->id;
	}

	function getRoleId(): string
	{
		if (in_array('gallery', $this->roles)) {
			return 'gallery';
		}

		if (in_array('member', $this->roles)) {
			return 'member';
		}

		return 'user';
	}

	/**
	 * @return string[]
	 */
	public function getRoles(): array
	{
		return $this->roles;
	}

	public function getData(): array
	{
		return [
 			$this->name,
 			$this->surname,
 			$this->mail,
 			$this->token,
 			$this->tokenExpiration,
		];
	}
}
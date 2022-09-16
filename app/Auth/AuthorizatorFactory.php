<?php


namespace App\Auth;


use Nette\Security\Permission;
use Tracy\Debugger;

class AuthorizatorFactory
{
	public static function create(): Permission
	{
		$acl = new Permission;

		$acl->addRole('guest');
		$acl->addRole('user', 'guest');
		$acl->addRole('member', 'user');

		$acl->addRole('gallery');

		$acl->addResource('album');

		$publicOnlyAssertion = function (Permission $acl, string $role, string $resource, string $privilege): bool {
			/**@var \App\Model\Album\Album $resource */
			$resource = $acl->getQueriedResource();

			return $resource->public;
		};

		$acl->allow('guest', 'album', 'view' , $publicOnlyAssertion);

		$acl->allow('user', 'album', 'view');

		$acl->allow('member', 'album', 'add');
		$acl->allow('member', 'album', 'upload');

		$authorOnlyAssertion = function (Permission $acl, string $role, string $resource, string $privilege): bool {
			/**@var \App\Auth\AuthUser $role */
			$role = $acl->getQueriedRole();

			/**@var \App\Model\Album\Album $resource */
			$resource = $acl->getQueriedResource();

			return $role->getId() === $resource->createdBy->id;
		};

		$acl->allow('member', 'album', 'edit', $authorOnlyAssertion);
		$acl->allow('member', 'album', 'delete', $authorOnlyAssertion);

		$acl->allow('gallery', 'album', $acl::ALL);

		return $acl;
	}
}
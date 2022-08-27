<?php


namespace App\Presenters;

use Nette\Application\UI\Presenter;
use Nette\Utils\ArrayHash;

abstract class BasePresenter extends Presenter
{
	protected function beforeRender() {
		parent::beforeRender();

		$this->template->mainMenu = ArrayHash::from([
			['title' => 'novinky',		'link' => 'Homepage:default',				'role' => NULL, 	'current' => $this->isLinkCurrent('Homepage:default')	],
			['title' => 'alba',			'link' => 'Homepage:albums',				'role' => NULL, 	'current' => ($this->isLinkCurrent('Album:*') || $this->isLinkCurrent('Homepage:albums'))	],
			['title' => 'přihlášení',	'link' => 'Sign:in',						'role' => 'guest',	'current' => $this->isLinkCurrent('Sign:*')	],
			['title' => 'intranet',		'link' => 'https://member.vzs-jablonec.lh',	'role' => 'user',	'current' => false	],
			['title' => 'odhlášení',	'link' => 'Sign:out',						'role' => 'user',	'current' => $this->isLinkCurrent('Sign:*')	],
		]);
	}
}
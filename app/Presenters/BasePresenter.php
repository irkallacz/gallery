<?php


namespace App\Presenters;

use App\Menu\Menu;
use Nette\Application\UI\Presenter;
use Nette\DI\Attributes\Inject;

abstract class BasePresenter extends Presenter
{
	#[Inject]
	public Menu $menu;

	protected function createComponentMenu(): Menu
	{
		return $this->menu;
	}
}
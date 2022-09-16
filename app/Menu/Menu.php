<?php


namespace App\Menu;


use Nette\Application\UI\Control;
use Nette\Schema\Expect;
use Nette\Schema\Processor;
use Nette\Utils\Html;
use Tracy\Debugger;

final class Menu extends Control
{
	/**
	 * @var MenuItem[]
	 */
	private array $items;

	public function __construct(array $items = [])
	{
		$processor = new Processor();

		$this->items = $processor->process(Expect::arrayOf(
			Expect::from(new MenuItem())
		), $items);
	}

	public function render()
	{
		$nav = Html::el('nav');
		$ul = $nav->create('ul', ['id' => 'mainMenu']);

		foreach ($this->items as $item) {
			if (!is_null($item->loggedIn)) {
				if ($this->presenter->user->isLoggedIn() != $item->loggedIn) {
					continue;
				}
			}

			$li = $ul->create('li');

			if ($item->current) {
				$currents = is_array($item->current) ? $item->current : [$item->current];

				foreach ($currents as $current) {
					if ($this->presenter->isLinkCurrent($current)) {
						$li->setAttribute('class', 'current');
						break;
					}
				}
			}

			$a = $li->create('a')
				->setText($item->title);

			if ($item->link) {
				$a->href = $item->link;
			}

			if ($item->action) {
				$a->href = $this->presenter->link($item->action);
			}

		}

		print $nav->render();
	}



}
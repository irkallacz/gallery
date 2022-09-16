<?php


namespace App\Menu;


final class MenuItem
{
	public string $title;

	public string | null $action = null;

	public string | null $link = null;

	public string | array | null $current = null;

	public bool | null $loggedIn = null;

}
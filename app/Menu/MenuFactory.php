<?php


namespace App\Menu;


interface MenuFactory
{
	public function create(): Menu;
}
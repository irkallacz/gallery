<?php


namespace App\Template;


use App\Model\Album\Album;
use App\Model\AlbumPhoto\AlbumPhoto;
use Nette\Security\User;

final class LayoutTemplate
{
	public AlbumPhoto $image;

	public Album $album;

	public User $user;
}
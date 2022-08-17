<?php


namespace App\Template;


use App\Model\Album\Album;
use App\Model\AlbumPhoto\AlbumPhoto;

class HomepageTemplate
{
	public string $baseUrl;

	public string $basePath;

	/**
	 * @var Album[]
	 */
	public array $albums;

	/**
	 * @var AlbumPhoto[]
	 */
	public array $photos;

}
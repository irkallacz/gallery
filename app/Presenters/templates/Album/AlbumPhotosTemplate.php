<?php


namespace App\Template;


use App\Model\AlbumPhoto\AlbumPhoto;
use Nextras\Orm\Collection\ICollection;

final class AlbumPhotosTemplate extends AlbumTemplate
{
	/**
	 * @var AlbumPhoto[] $photos
	 */
	public ICollection $photos;

	public string $originalPath;

	public string $largePath;

	public string $mediumPath;
}
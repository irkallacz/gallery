<?php

namespace App\Photo;

use Nette\Http\FileUpload;
use Nette\Utils\Strings;
use Tracy\Debugger;

final class PhotoService
{
	const PHOTO_TYPE_ORIGINAL = 'original';
	const PHOTO_TYPE_LARGE = 'large';
	const PHOTO_TYPE_MEDIUM = 'medium';
	const PHOTO_TYPE_SMALL = 'small';

	private string $wwwDir;
	private string $albumsDir;

	private array $photoDimensions;

	/**
	 * PhotoService constructor.
	 * @param string $wwwDir
	 * @param string $albumsDir
	 * @param array $photoDimensions
	 */
	public function __construct(string $wwwDir, string $albumsDir, array $photoDimensions)
	{
		$this->wwwDir = $wwwDir;
		$this->albumsDir = $albumsDir;
		$this->photoDimensions = $photoDimensions;
	}

	public function getPhotoPath(int $albumId, string $type = null, string $fileName = null, bool $relative = false): string
	{
		$items = array_filter([$fileName, $type, $albumId, $this->albumsDir]);

		if (!$relative) {
			$items[] = $this->wwwDir;
		}

		return join(DIRECTORY_SEPARATOR, array_reverse($items));
 	}

 	public function getRelativePhotoPath(int $albumId, string $type = self::PHOTO_TYPE_ORIGINAL): string
	{
		return $this->getPhotoPath($albumId, $type, null, true);
	}

 	private function getPhotoDimensions(string $type, bool $horizontal = false): array
	{
		$dimensions = $this->photoDimensions[$type];

		if ($horizontal) {
			//krsort($dimensions);
			$dimensions = array_reverse($dimensions);
		}

		return $dimensions;
	}

	/**
	 * @return string[]
	 * @throws \ImagickException
	 */
	public function uploadPhoto(FileUpload $fileUpload, int $albumId): array
	{
		$fileName = $fileUpload->getSanitizedName();

		$filePath = $this->getPhotoPath($albumId, self::PHOTO_TYPE_ORIGINAL, $fileName);
		$fileUpload->move($filePath);

		$thumbName = self::getThumbName($fileName);

		$this->transformPhoto($albumId, $filePath, $thumbName);

		return [$fileName, $thumbName];
	}

	public function transformPhoto(int $albumId, string $filePath, string $thumbName): void
	{
		$image = new \Imagick($filePath);

		$this->fixPhotoOrientation($image);
		$this->generateThumbnails($albumId, $image, $thumbName);
	}

	public static function getThumbName(string $fileName): string
	{
		return Strings::lower(pathinfo($fileName, PATHINFO_FILENAME)) . '.webp';
	}

	private function generateThumbnails(int $albumId, \Imagick $image, string $thumbName): void
	{
		$imageDimension = [$image->getImageWidth(), $image->getImageHeight()];
		$horizontal = $imageDimension[0] < $imageDimension[1];

		$image->setImageFormat('webp');
		$image->setOption('webp:method', '6');
		$image->setImageCompressionQuality(50);

		foreach ([self::PHOTO_TYPE_LARGE, self::PHOTO_TYPE_MEDIUM] as $type) {
			$dimensions = $this->getPhotoDimensions($type, $horizontal);
			if (max($imageDimension) > max($dimensions)) {
				$image->adaptiveResizeImage(...$dimensions);
				$imageDimension = $dimensions;
			}

			$image->writeImage($this->getPhotoPath($albumId, $type, $thumbName));
		}

		$image->cropThumbnailImage(...$this->getPhotoDimensions(self::PHOTO_TYPE_SMALL));
		$image->writeImage($this->getPhotoPath($albumId, self::PHOTO_TYPE_SMALL, $thumbName));
	}

	private function fixPhotoOrientation(\Imagick &$image): bool
	{
		switch ($image->getImageOrientation()) {
			case \Imagick::ORIENTATION_TOPRIGHT:
				$image->flopImage();
				$image->setImageOrientation(\Imagick::ORIENTATION_TOPLEFT);
				return true;
			case \Imagick::ORIENTATION_BOTTOMRIGHT:
				$image->rotateImage("#000", 180);
				$image->setImageOrientation(\Imagick::ORIENTATION_TOPLEFT);
				return true;
			case \Imagick::ORIENTATION_BOTTOMLEFT:
				$image->flopImage();
				$image->rotateImage("#000", 180);
				$image->setImageOrientation(\Imagick::ORIENTATION_TOPLEFT);
				return true;
			case \Imagick::ORIENTATION_LEFTTOP:
				$image->flopImage();
				$image->rotateImage("#000", -90);
				$image->setImageOrientation(\Imagick::ORIENTATION_TOPLEFT);
				return true;
			case \Imagick::ORIENTATION_RIGHTTOP:
				$image->rotateImage("#000", 90);
				$image->setImageOrientation(\Imagick::ORIENTATION_TOPLEFT);
				return true;
			case \Imagick::ORIENTATION_RIGHTBOTTOM:
				$image->flopImage();
				$image->rotateImage("#000", 90);
				$image->setImageOrientation(\Imagick::ORIENTATION_TOPLEFT);
				return true;
			case \Imagick::ORIENTATION_LEFTBOTTOM:
				$image->rotateImage("#000", -90);
				$image->setImageOrientation(\Imagick::ORIENTATION_TOPLEFT);
				return true;
			default: // Invalid orientation
				return FALSE;
		}
	}

	public function getPhotoDate(int $albumId, string $fileName): ?\DateTimeImmutable
	{
		$ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

		if (in_array($ext, ['jpg', 'jpeg'])) {
			if ($exif = @exif_read_data($this->getPhotoPath($albumId, self::PHOTO_TYPE_ORIGINAL, $fileName)) ?: null) {
				foreach (['DateTime', 'DateTimeOriginal', 'DateTimeDigitized', 'FileDateTime'] as $field) {
					if (array_key_exists($field, $exif)) {
						return new \DateTimeImmutable($exif[$field]);
					}
				}
			}
		}

		return null;
	}
}
<?php

namespace App\Image;

use Nette\Http\FileUpload;
use Nette\Utils\Strings;
use Tracy\Debugger;

final class ImageService
{
	const IMAGE_TYPE_ORIGINAL = 'original';
	const IMAGE_TYPE_LARGE = 'large';
	const IMAGE_TYPE_MEDIUM = 'medium';
	const IMAGE_TYPE_SMALL = 'small';

	const IMAGE_MIME_TYPES = ['image/gif', 'image/png', 'image/jpeg', 'image/webp', 'image/heic', 'image/avif'];

	private string $wwwDir;
	private string $albumsDir;
	private array $imageDimensions;

	/**
	 * PhotoService constructor.
	 * @param string $wwwDir
	 * @param string $albumsDir
	 * @param array $imageDimensions
	 */
	public function __construct(string $wwwDir, string $albumsDir, array $imageDimensions)
	{
		$this->wwwDir = $wwwDir;
		$this->albumsDir = $albumsDir;
		$this->imageDimensions = $imageDimensions;
	}

	public function getImagePath(int $albumId, string $type = null, string $fileName = null, bool $relative = false): string
	{
		$items = array_filter([$fileName, $type, $albumId, $this->albumsDir]);

		if (!$relative) {
			$items[] = $this->wwwDir;
		}

		return join(DIRECTORY_SEPARATOR, array_reverse($items));
 	}

 	public function getRelativeImagePath(int $albumId, string $type = self::IMAGE_TYPE_ORIGINAL): string
	{
		return $this->getImagePath($albumId, $type, null, true);
	}

 	private function getImageDimensions(string $type, bool $horizontal = false): array
	{
		$dimensions = $this->imageDimensions[$type];

		if ($horizontal) {
			//krsort($dimensions);
			$dimensions = array_reverse($dimensions);
		}

		return $dimensions;
	}

	public function createDirectories(int $albumId)
	{
		foreach ([self::IMAGE_TYPE_ORIGINAL, self::IMAGE_TYPE_LARGE, self::IMAGE_TYPE_MEDIUM, self::IMAGE_TYPE_SMALL] as $type) {
			mkdir($this->getImagePath($albumId, $type), 0777, true);
		}
	}

	/**
	 * @return string[]
	 * @throws \ImagickException
	 */
	public function uploadImage(FileUpload $fileUpload, int $albumId): array
	{
		$fileName = $fileUpload->getSanitizedName();

		$filePath = $this->getImagePath($albumId, self::IMAGE_TYPE_ORIGINAL, $fileName);
		$fileUpload->move($filePath);

		$thumbName = self::getThumbName($fileName);

		$this->transformImage($albumId, $filePath, $thumbName);

		return [$fileName, $thumbName];
	}

	public function transformImage(int $albumId, string $filePath, string $thumbName): void
	{
		$image = new \Imagick($filePath);

		$this->fixImageOrientation($image);
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

		foreach ([self::IMAGE_TYPE_LARGE, self::IMAGE_TYPE_MEDIUM] as $type) {
			$dimensions = $this->getImageDimensions($type, $horizontal);
			if (max($imageDimension) > max($dimensions)) {
				$image->adaptiveResizeImage(...$dimensions);
				$imageDimension = $dimensions;
			}

			$image->writeImage($this->getImagePath($albumId, $type, $thumbName));
		}

		$image->cropThumbnailImage(...$this->getImageDimensions(self::IMAGE_TYPE_SMALL));
		$image->writeImage($this->getImagePath($albumId, self::IMAGE_TYPE_SMALL, $thumbName));
	}

	private function fixImageOrientation(\Imagick &$image): bool
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
				return false;
		}
	}

	public function isImage(FileUpload $file): bool
	{
		return in_array($file->getContentType(), self::IMAGE_MIME_TYPES, true);
	}

	public function getImageDate(int $albumId, string $fileName): ?\DateTimeImmutable
	{
		$ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

		if (in_array($ext, ['jpg', 'jpeg'])) {
			if ($exif = @exif_read_data($this->getImagePath($albumId, self::IMAGE_TYPE_ORIGINAL, $fileName)) ?: null) {
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
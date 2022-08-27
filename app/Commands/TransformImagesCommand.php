<?php declare(strict_types = 1);

namespace App\Commands;

use App\Model\Album\AlbumsRepository;
use App\Model\AlbumPhoto\AlbumPhotosRepository;
use App\Photo\ImageService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class TransformImagesCommand extends Command
{

	private ImageService $photoService;

	private AlbumsRepository $albumsRepository;

	protected static $defaultName = 'photos:transform';

	/**
	 * TransformImagesCommand constructor.
	 * @param ImageService $photoService
	 * @param AlbumsRepository $albumsRepository
	 */
	public function __construct(ImageService $photoService, AlbumsRepository $albumsRepository)
	{
		parent::__construct();

		$this->photoService = $photoService;
		$this->albumsRepository = $albumsRepository;
	}

	protected function configure(): void
	{
		// choose command name
		$this->setName(self::$defaultName)
			// description (optional)
			->setDescription('Transform images from old system to new one')
			// arguments (maybe required or not)
			->addArgument('albums', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Limit to only this albums ID');
		// you can list options as well (refer to symfony/console docs for more info)
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$albums = $input->getArgument('albums');
		$albums = count($albums) ? $this->albumsRepository->findByIds($albums) : $this->albumsRepository->findAll();

		foreach ($albums as $album) {
			$output->writeln($album->slug);

			foreach ([ImageService::PHOTO_TYPE_LARGE, ImageService::PHOTO_TYPE_MEDIUM, ImageService::PHOTO_TYPE_SMALL, ImageService::PHOTO_TYPE_ORIGINAL] as $type) {
				$path = $this->photoService->getPhotoPath($album->id, $type);

				if (!file_exists($path)) {
					//TODO
					mkdir($path, 0755);
					$output->writeln($path);
				}
			}

			foreach ($album->photos as $photo) {
				$oldPath = $this->photoService->getPhotoPath($album->id, null, $photo->filename);
				$newPath = $this->photoService->getPhotoPath($album->id, ImageService::PHOTO_TYPE_ORIGINAL, $photo->filename);

				if (file_exists($oldPath)) {
					$backupPath = $oldPath . '_';
					$filePath = (file_exists($backupPath)) ? $backupPath : $oldPath;
					link($filePath, $newPath);
				} elseif (file_exists($newPath)) {
					$filePath = $newPath;
				} else {
					continue;
				}

				$this->writeln($output, (string) $album->id, $photo->filename, $photo->thumbname);

				$this->photoService->transformPhoto($album->id, $filePath, $photo->thumbname);
			}
		}

		return 0;
	}

	private function writeln(OutputInterface $output, string ...$arguments): void
	{
		$output->writeln(join("\t", $arguments));
	}

}
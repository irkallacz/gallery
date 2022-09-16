<?php


namespace App\Model\Log;

use Nextras\Orm\Repository\Repository;
use Nextras\Orm\Collection\ICollection;

final class LogsRepository extends Repository
{
	public static function getEntityClassNames(): array
	{
		return [Log::class];
	}

}
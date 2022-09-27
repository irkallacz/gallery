<?php


namespace App\Model\Right;

use Nextras\Orm\Repository\Repository;

final class RightsRepository extends Repository
{
	public static function getEntityClassNames(): array
	{
		return [Right::class];
	}
}
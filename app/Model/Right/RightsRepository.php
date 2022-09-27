<?php


namespace App\Model\Right;


final class RightsRepository extends Repository
{
	public static function getEntityClassNames(): array
	{
		return [Right::class];
	}
}
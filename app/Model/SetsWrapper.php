<?php


namespace App\Model;


use Nextras\Orm\Entity\ImmutableValuePropertyWrapper;

final class SetsWrapper extends ImmutableValuePropertyWrapper
{

	public function convertToRawValue($value): string
	{
		return join(',', $value);
	}

	public function convertFromRawValue($value): array
	{
		return explode(',', $value);
	}
}
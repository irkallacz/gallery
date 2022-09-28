<?php


namespace App\Model\Person;


use App\Model\Right\RightsMapper;
use Nextras\Orm\Entity\Reflection\PropertyMetadata;
use Nextras\Orm\Mapper\IMapper;
use Nextras\Orm\Mapper\Mapper;

final class PersonsMapper extends Mapper
{
	protected $tableName = 'user';

	public function getManyHasManyParameters(PropertyMetadata $sourceProperty, IMapper $targetMapper): array
	{
		if ($targetMapper instanceof RightsMapper) {
			return ['user_rights', ['user_id', 'rights_id']];
		}
		return parent::getManyHasManyParameters($sourceProperty, $targetMapper);
	}
}
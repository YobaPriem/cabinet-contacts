<?php

namespace App\Bundle\Contacts\Repository;

class City extends Repository
{
	protected $params = [
		'name' => ['%UF_NAME', '%UF_NAME_EN'],
		'id' => 'ID',
		'primary' => 'UF_PRIMARY',
		'regionId' => 'UF_REGION',
		'countryId' => 'UF_COUNTRY',
	];

	protected function hlBlockName()
	{
		return 'Contactscity';
	}

	public function getItems($params = array()) {
		$hlBlock = \TAO::highloadblock($this->hlBlockName());
		$filter = $this->prepareParams($params);
		$list = $hlBlock->getRows([
			'filter' => $filter,
			'order' => array('UF_SORT'),
//			'cache' => array('ttl' => 86400),
		]);

		$result = [];
		foreach ($list as $item) {
			if (!empty($item->property('UF_NAME')->value())) {
				$result[] = [
					'id' => $item->id(),
					'name' => $item->property('UF_NAME')->value(),
//					'regionName' => $item->property('UF_REGION')->value()->property('UF_NAME')-> value(),
					'regionId' => $item->property('UF_REGION')->valueRaw(),
//					'countryName' => $item->property('UF_COUNTRY')->value()->property('UF_NAME')-> value(),
					'countryId' => $item->property('UF_COUNTRY')->valueRaw(),
				];
			}
		}

		return $result;
	}
}
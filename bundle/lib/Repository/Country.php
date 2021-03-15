<?php

namespace App\Bundle\Contacts\Repository;

class Country extends Repository
{
	protected $params = [
		'name' => ['UF_NAME', 'UF_NAME_EN'],
		'id' => 'ID',
		'code' => 'UF_ISO',
		'primary' => 'UF_PRIMARY',
	];

	protected function hlBlockName()
	{
		return 'Contactscountry';
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
				];
			}
		}

		return $result;
	}
}
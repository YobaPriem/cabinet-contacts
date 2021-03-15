<?php

namespace App\Bundle\Contacts\Repository;

class Region extends Repository
{
	protected $params = [
		'name' => ['UF_NAME', 'UF_NAME_EN'],
		'id' => 'ID',
		'code' => 'UF_ISO',
	];

	protected function hlBlockName()
	{
		return 'Contactsregion';
	}

	public function getItems($params = array()) {
		$hlBlock = \TAO::highloadblock($this->hlBlockName());
		$filter = $this->prepareParams($params);
		$list = $hlBlock->getRows([
			'filter' => $filter,
//			'cache' => array('ttl' => 86400),
		]);

		$result = [];
		foreach ($list as $item) {
			if (!empty($item->property('UF_NAME')->value())) {
				$result[] = [
					'id' => $item->id(),
					'name' => $item->property('UF_NAME')->value(),
					'countryId' => $item->property('UF_COUNTRY')->valueRaw(),
				];
			}
		}

		return $result;
	}
}
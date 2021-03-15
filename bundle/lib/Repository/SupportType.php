<?php

namespace App\Bundle\CabinetContacts\Repository;

use App\Bundle\CabinetContacts\RepositoryFactory;

class SupportType extends Repository
{
	protected $params = [];

	protected function hlBlockName()
	{
		return 'Contactssupporttype';
	}

	public function getItems($params = array()) {
		$hlBlock = \TAO::highloadblock($this->hlBlockName());
		$filter = [];
		if ($params['limited'] == true) {
			$filter = [
				'UF_COMMON' => true
			];
		}
		$list = $hlBlock->getRows(['filter' => $filter]);

		$result = [];
		$questionRepo = RepositoryFactory::factory('SupportQuestion');
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

<?php

namespace App\Bundle\CabinetContacts\Repository;

use App\Bundle\Contacts\RepositoryFactory;

class SupportQuestion extends Repository
{
	protected $params = [
		'supportTypeId' => 'UF_SUPPORT_TYPE',
	];

	protected function hlBlockName() {
		return 'Contactssupportquestion';
	}

	public function getItems($params = array()) {
		$hlBlock = \TAO::highloadblock($this->hlBlockName());
		$filter = $this->prepareParams($params);
		$list = $hlBlock->getRows([
			'filter' => $filter
		]);

		$result = [];
		$specialistsRepo = RepositoryFactory::factory('SupportSpecialist');
		foreach ($list as $item) {
			$specialistsCount = $specialistsRepo->getCount(array_merge($params, ['questionId' => $item->id()]));
			if ($specialistsCount > 0) {
//				$orientations = [];
//				foreach ($item->property('UF_ORIENTATIONS')->value() as $orientation) {
//					$orientations[] = $orientation->property('UF_NAME')->value();
//				}
				$preparedItem = [
					'id' => $item->id(),
					'name' => $this->getMultilangFieldValue('UF_NAME', $item, $params['lang']),
//					'supportType' => $item->property('UF_SUPPORT_TYPE')->value()->property('UF_NAME')->value(),
//					'orientations' => $orientations,
				];
				if ($icon = $item->property('UF_ICON')->value()) {
					$preparedItem['icon'] = $icon->url();
				}

				// if ($cabIcon = $item->property('UF_CABINET_ICON')->value()) {
				// 	$preparedItem['cabinetIcon'] = $cabIcon->url();
				// }

				$result[] = $preparedItem;
			}
		}

		return $result;
	}
}

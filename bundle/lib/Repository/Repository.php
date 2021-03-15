<?php

namespace App\Bundle\CabinetContacts\Repository;

abstract class Repository {
	protected $params = [];

	abstract protected function hlBlockName();
	abstract public function getItems($params = array());

	protected function prepareParams($params = array()) {
		$filter = [];
		foreach($this->params as $paramName => $filterNames) {
			if (isset($params[$paramName])) {
				if (is_array($filterNames)) {
					$orFilter = [
						'LOGIC' => 'OR',
					];
					foreach ($filterNames as $filterName) {
						$orFilter[$filterName] = $params[$paramName];
					}
					$filter[] = $orFilter;
				} else {
					$filter[$filterNames] = $params[$paramName];
				}
			}
		}

		return $filter;
	}

	public function getCount($filter) {
		$hlBlock = \TAO::highloadblock($this->hlBlockName());
		$list = $hlBlock->getRows([
			'select' => [
				new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(*)')
			],
			'filter' => $filter
		]);

		return intval($list[0]->getCalculatedValues()['CNT']);
	}

	public function getMultilangFieldValue($fieldName, $item, $lang = 'ru') {
		if ($lang !== 'ru') {
			$newFieldName = $fieldName . '_' . strtoupper($lang);
			$field = $item->property($newFieldName);
			if (!is_null($field) && $field->value()) {
				return $field->value();
			}
		}

		return $item->property($fieldName)->value();
	}
}

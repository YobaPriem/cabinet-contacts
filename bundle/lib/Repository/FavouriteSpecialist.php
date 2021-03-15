<?php

namespace App\Bundle\CabinetContacts\Repository;

use App\Bundle\Contacts\Repository\SupportSpecialist as RepositorySupportSpecialist;

class FavouriteSpecialist
{
	public function getItems($params = [], $limit = 0, $offset = 0)
	{
		global $USER;

		$ids = [];
		$selectedQuestion = '';

		$parameters = [
			'order' => ['TIMESTAMP_X' => 'DESC'],
			'filter' => [
				'>PROPERTY_IBLOCK' => '0',
				'>PROPERTY_ELEMENT' => 0,
				'=PROPERTY_USER' => $USER->GetID(),
			],
		];

		$favItems = \TAO::infoblock('Favourites')->getItems($parameters);

		foreach($favItems as $item) {
			$ids[] = $item->property('ELEMENT')->value();
		}

		$filter[] = $this->prepareParams($params);

		$filter['ID'] = $ids;

		$selectedQuestion = $params['questionId'];

		$repository = new RepositorySupportSpecialist();

		$items = $repository->getSpecialistsByFilter($filter, $limit, $offset);

		if ($items) {
			$items = $this->getTeamInfo($items, $ids, $selectedQuestion);
		}

		if (!$selectedQuestion) {
			$count = count($repository->getSpecialistsByFilter($filter, 0));
		}else {
			$count = count($items);
		}


		return [
			'items' => $items,
			'amountOfSpecialists' => $count,
		];
	}

	private function getTeamInfo($items, $ids, $selectedQuestion) {
		$hlBlock = \TAO::highloadblock('Contactssupportteam');
		$params = [
			'filter' => [
				'UF_SPECIALISTS' => $ids,
			]
		];

		$list = $hlBlock->getRows($params);

		foreach ($list as $item) {
			foreach($item->property('UF_SPECIALISTS')->value() as $specialist) {
				$key = array_search($specialist->id(), array_column($items, 'id'));

				if ($key !== false) {
					if ($item->property('UF_CITIES')->value() !== null) {
						$items[$key]['location'] = $item->property('UF_CITIES')->value()[0]->property('UF_NAME')->value();
					}

					if ($item->property('UF_REGIONS')->value() !== null) {
						// var_dump($item->property('UF_REGIONS')->value());
					}

					if ($item->property('UF_QUESTIONS')->value() ) {
						foreach($item->property('UF_QUESTIONS')->value() as $question) {
							if ($selectedQuestion && $items[$key]['searched'] == false) {
								$isSearched = false;

								if ($selectedQuestion == $question->id()) {
									$isSearched = true;
								}
							} else {
								$isSearched = true;
							}

							$items[$key]['searched'] = $isSearched;

							$items[$key]['questions'][] = $question->id();
						}
					}
				}
			}
		}

		$items = $this->unsetNotInSearch($items);

		return $items;
	}

	private function unsetNotInSearch($items) {
		foreach($items as $key=>$item) {
			if (!$item['searched']) {
				unset($items[$key]);
			}
		}

		return $items;
	}

	protected function prepareParams($params = array())
	{
		$filter = [];
		if (isset($params['searchStr'])) {
			$searchArr = explode(' ', $params['searchStr']);
			$filterFields = [
				'%UF_NAME',
				'%UF_SURNAME',
				'%UF_PATRONYMIC',
			];
			$permutations = $this->computePermutations($filterFields);

			$permFilters = [];
			foreach ($permutations as $permutation) {
				$permFilter = [];
				for ($i = 0; $i < count($searchArr); $i++) {
					$permFilter[$permutation[$i]] = $searchArr[$i];
				}
				$permFilters[] = $permFilter;
			}

			$filter = array_merge(['LOGIC' => 'OR'], $permFilters);
		}

		return $filter;
	}

	protected function computePermutations($array) {
		$result = [];

		$recurse = function($array, $start_i = 0) use (&$result, &$recurse) {
			if ($start_i === count($array)-1) {
				array_push($result, $array);
			}

			for ($i = $start_i; $i < count($array); $i++) {
				//Swap array value at $i and $start_i
				$t = $array[$i]; $array[$i] = $array[$start_i]; $array[$start_i] = $t;

				//Recurse
				$recurse($array, $start_i + 1);

				//Restore old order
				$t = $array[$i]; $array[$i] = $array[$start_i]; $array[$start_i] = $t;
			}
		};

		$recurse($array);

		return $result;
	}
}

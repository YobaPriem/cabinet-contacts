<?php

namespace App\Bundle\CabinetContacts\Controller;

use Bitrix\Main\Application;
use TAO\Controller;
use App\Bundle\CabinetContacts\RepositoryFactory;
use \Bitrix\Main\Data\Cache;


/**
 * Контроллер корзины,
 * проверяет входящие данные и запускает нужный функционал основоного модуля работы с корзиной (BitrixBasket)
 *
 * Class CreateOrders
 * @package App\Bundle\Cart\Controller
 */
class AjaxContacts extends Controller
{
	/**
	 * @var \Bitrix\Main\HttpRequest
	 */
	protected $request;

	public function __construct()
	{
		try {
			$this->request = Application::getInstance()->getContext()->getRequest();
		} catch (\Exception $e) {

		}
	}

	public function index($method = '')
	{
		$method = $this->prepareMethod($method);

		if (empty($method) || !method_exists($this, $method)) {
			return $this->pageNotFound();
		}

		$params = $this->request->getQueryList()->toArray();
		return $this->renderJSON($this->$method($params));
	}

	protected function prepareMethod($method) {
		$method = strip_tags(trim($method));
		$words = explode('-', $method);
		$method = '';

		$isFirst = true;
		foreach($words as $word) {
			if ($isFirst) {
				$method .= $word;
			} else {
				$method .= ucfirst($word);
			}

			$isFirst = false;
		}

		return $method;
	}

	protected function initPageData() {
		$repo = RepositoryFactory::factory('FavouriteSpecialist');
		$specialistsData = $repo->getItems([], 9);
		$data['specialists'] = $specialistsData['items'];
		$data['amountOfPages'] = ceil($specialistsData['amountOfSpecialists'] / 9);
		$data['supportTypes'] = $this->getSupportTypes();
		$data['currentSupportType'] = current($data['supportTypes']);

		$newParams = [
			'supportTypeId' => $data['currentSupportType']['id'],
		];

		// $data['amountOfPages'] =

		$data['supportQuestions'] = $this->getSupportQuestions($newParams);


		return $data;
	}

	protected function getSupportTypes() {
		$repo = RepositoryFactory::factory('SupportType');

		$params = [
			'limited' => 1,
		];

		return $repo->getItems($params);
	}

	protected function getSupportQuestions($params = []) {
		$repo = RepositoryFactory::factory('SupportQuestion');
		return $repo->getItems($params);
	}

	protected function getSupportSpecialists($params = []) {
		$repo = RepositoryFactory::factory('FavouriteSpecialist');

		if ($params['page']) {
			$offset = ($params['page'] - 1) * 9 - 1;
			unset($params['page']);
		}

		$specialistsData = $repo->getItems($params, 9, $offset);
		$data['specialists'] = $specialistsData['items'];
		$data['amountOfPages'] = ceil($specialistsData['amountOfSpecialists'] / 9);

		return $data;
	}

	protected function renderJSON($value = '', $success = true)
	{
		$this->noLayout();
		header('Content-Type: application/json');
		echo json_encode(
			[ 'success' => $success, 'dataSet' => $value ],
			JSON_UNESCAPED_UNICODE
		);
		die;
	}
}

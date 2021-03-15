<?php

namespace App\Bundle\CabinetContacts;

class Bundle extends \TAO\Bundle
{
	public function routes()
	{
		return [
			'{^' . SITE_DIR . 'cabinet-contacts-api/([^/]+)/$}' => array('{1}', 'action' => 'index', 'controller' => 'AjaxContacts'),
		];
	}
}

class HightloadBlockException extends \Exception
{
}

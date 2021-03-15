<?php

namespace App\Bundle\CabinetContacts;

class RepositoryFactory
{
	public static function factory($repName) {

		$className = '\\App\\Bundle\\CabinetContacts\\Repository\\' . $repName;

		if (class_exists($className)) {
			return new $className;
		} else {
			throw new RepositoryException('Нет репозитория для '. $repName);
		}
	}
}

class RepositoryException extends \Exception {}

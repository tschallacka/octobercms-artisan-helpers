<?php namespace Tschallacka\Artisan\Classes;

use System\Classes\VersionManager as VMBase;

class VersionManager extends VMBase {
	public function getLatestFileVersion($code) {
		return parent::getLatestFileVersion($code);
	}
	public function setDatabaseVersion($code,$version=null) {
		return parent::setDatabaseVersion($code,$version);
	}
	public function getDatabaseVersion($code) {
		return parent::getDatabaseVersion($code);
	}
}
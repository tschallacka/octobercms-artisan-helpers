<?php namespace Tschallacka\Artisan\Console;

use Db;
use InvalidArgumentException;
use Illuminate\Console\Command;

use System\Classes\UpdateManager;
use System\Classes\PluginManager;
//use System\Classes\VersionManager;
use Tschallacka\Artisan\Classes\VersionManager;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;


class PluginVersionShift extends Command
{
    /**
     * @var string The console command name.
     */
    protected $name = 'tschallacka:pluginversionshift';

    /**
     * @var string The console command description.
     */
    protected $description = 'Shifts plugin up to desired version up or down';

    protected $pluginName;
    protected $pluginVersion;
    
    private function validatePlugin() {
    	if (!PluginManager::instance()->exists($this->pluginName)) {
    		throw new InvalidArgumentException(sprintf('Plugin "%s" not found.', $this->pluginName));
    	}
    	return true;
    }
    
    private function validateVersion() {
    	if(!$this->isVersionNumberValid($this->pluginVersion)) {
    		throw new InvalidArgumentException(sprintf('Version is invalid! there is no version number with this number in the plugin history.', $this->pluginName));
    	}
    	return true;
    }
    private function log($str) {
    	$this->output->writeln($str);
    }
    
    private $currentVersion;
    
    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
    	$pluginName = $this->argument('name');
    	
    	$this->pluginVersion = $this->argument('version');
    	
    	$this->pluginName = PluginManager::instance()->normalizeIdentifier($pluginName);
    	
    	$versionManager = VersionManager::instance();
    	
    	$this->currentVersion = $versionManager->getDatabaseVersion($this->pluginName);
    	
    	
    	$commands = ['up','down','reload','refresh'];
    	if(!in_array($this->pluginVersion,$commands)) {
    		$this->versonNumberShift();
    	}
    	else {
    		if($this->pluginVersion == 'reload' || $this->pluginVersion ==  'refresh') {
    			$this->reload();
    		}
    		elseif($this->pluginVersion == 'up') {
    			$this->up();
    		}
    		elseif($this->pluginVersion == 'down') {
    			 $this->down();
    		}
    		else {
    			$this->log('eeehhhh, what\'s up doc?');
    		}
    	}
    }
    
    public function up() {
    	$versionManager = VersionManager::instance();
    	$future = $versionManager->listNewVersions($this->pluginName);
    	reset($future);
    	$key = key($future);
    	if(!is_null($key)) { 
    		$this->log(dump("Current: ". $this->currentVersion. " going to $key"));
    		$this->pluginVersion = $key;
    		$this->versonNumberShift();
    	}
    	else {
    		$this->log("You know how they say, there's no way but up? well, not anymore.");
    	}
    }
    
    public function down() {
    	$version = $this->previousVersion();
    	if($version) {
    		$this->pluginVersion = $version;
    		$this->log(dump("Current: ". $this->currentVersion. " going to $version"));
    		$this->versonNumberShift();
    	}
    	else {
    		$this->log('You\'re at the bottom... there is no more down.');
    	}
    }
    
    public function reload() {
    	$version = $this->previousVersion();
    	if($version) {
    		$current = $this->currentVersion;
    		$this->down();
    		$this->currentVersion = $version;
    		$this->pluginVersion = $current;
    		$this->versonNumberShift();
    	}
    	else {
    		$current = $this->currentVersion;
    		$manager = UpdateManager::instance()->resetNotes();
    		$manager->rollbackPlugin($this->pluginName);
    		foreach ($manager->getNotes() as $note) {
    			$this->output->writeln($note);
    		}
    		$this->currentVersion = 0;
    		$this->up();
    		
    		
    	}
    }
    private function versonNumberShift() {
    	
    	$versionManager = VersionManager::instance();
    	$plugin = PluginManager::instance()->findByIdentifier($this->pluginName);
    	$this->log("Current plugin version: ".$this->currentVersion);
    	 
    	//$this->log(dump(get_class_methods(get_class($plugin))));
    	//$this->log(dump($plugin->pluginDetails()));
    	
    	if($this->validatePlugin() && $this->validateVersion()) {
    		if($this->shiftDirection == 'history') {
    			$this->log("History correction: ".$this->historyCorrection);
    			$this->log("Shifting into the history. Please note only actions as defined in the version.yaml will be executed!");
    			$versionManager->removePlugin($this->pluginName, $this->historyCorrection);
    		}
    		elseif($this->shiftDirection == 'future') {
    			$this->log("Shifting into the future. Please note only actions as defined in the version.yaml will be executed!");
    			$versionManager->updatePlugin($this->pluginName, $this->pluginVersion);
    		}
    		else {
    			$this->log('No change needed.');
    		}
    	}
    	else {
    		$this->log('Impossibruuuu');
    	}
    	//
    	 
    	foreach ($versionManager->getNotes() as $note) {
    		$this->output->writeln($note);
    	}
    	 
    	$this->log("done. Current version is now at ".$versionManager->getDatabaseVersion($this->pluginName));
    }
    public function previousVersion() {
    	$history = $this->getHistoryInfo();
    	$versions = [];
    	foreach ($history as $info) {
    		$versions[] = $info->version;
    	}
    	$versions = array_reverse($versions);
    	$ret = null;
    	foreach ($versions as $info) {
    		if($this->isVersionLess($info, $this->currentVersion)) {
    			return $info;
    		}
    		
    	}
    	return null;
    	
    }
    private $shiftDirection = null;
    private $historyCorrection = null;
    private function isVersionNumberValid($version) 
    {
    	if($this->pluginVersion == $this->currentVersion) {
    		return true;
    	}
    	$history = $this->getHistoryInfo();
    	$versions = [];
    	foreach ($history as $info) {
    		
    		$versions[] = $info->version;
    		
    	}
    	$versions = array_reverse($versions);
    	
    	foreach ($versions as $info) {
    		if($this->isVersionLess($info, $this->currentVersion)) {
	    		if($info == $version) {
	    			$this->shiftDirection = 'history';
	    			return true;
	    		}
    		}
    		$this->historyCorrection = $info;
    	}
    	
    	$versionManager = VersionManager::instance();
    	$future = $versionManager->listNewVersions($this->pluginName);
    	foreach($future as $futureVersion => $comments) {
    		
    		if($futureVersion == $version) {
    			$this->shiftDirection = 'future';
    			return true;
    		}
    	}
    	
    	
    	
    	return false;
    }
    
    private function getHistoryInfo() {
    	static $historyInfo;
    	if(is_null($historyInfo)) {
    		$historyInfo = Db::table('system_plugin_history')->where('code', $this->pluginName)->orderBy('id')->get();
    	}
    	return $historyInfo;
    }
    
    private function isVersionLess($version, $comparedTo) 
    {
    	$v1 = explode('.',$version);
    	$v2 = explode('.',$comparedTo);
    	foreach ($v1 as $key => $value) {
    		if($value < $v2[$key]) {
    			return true;
    		}
    		elseif($value > $v2[$key]){
    			return false;
    		}
    	}
    }

    /**
     * Get the console command arguments.
     * @return array
     */
    protected function getArguments()
    {
    	return [
    			['name', InputArgument::REQUIRED, 'The name of the plugin. Eg: AuthorName.PluginName'],
    			['version', InputArgument::REQUIRED, 'The version number to shift to or the words "up" or "down". If you enter "reload"  or "refresh"  then the current active version will first shifted down, and then be shifted back to current version.']
    	];
    }

    /**
     * Get the console command options.
     * @return array
     */
    protected function getOptions()
    {
        return [];
    }
}

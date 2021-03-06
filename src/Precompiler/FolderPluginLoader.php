<?php

namespace Precompiler;

use pocketmine\plugin\PluginLoader;
use pocketmine\Server;
use pocketmine\plugin\Plugin;
use pocketmine\utils\MainLogger;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\PluginDescription;
use pocketmine\plugin\PluginBase;
use pocketmine\event\plugin\PluginEnableEvent;
use pocketmine\event\plugin\PluginDisableEvent;

class FolderPluginLoader implements PluginLoader {
	/** @var Server */
	private $server;
	
	/**
	 *
	 * @param Server $server        	
	 */
	public function __construct() {
		$this->server = Server::getInstance ();
	}
	/**
	 * Loads the plugin contained in $file
	 *
	 * @param string $file        	
	 *
	 * @return Plugin
	 */
	public function loadPlugin($file) : void {
		if (is_dir ( $file ) and file_exists ( $file . "/plugin.yml" ) and file_exists ( $file . "/src/" )) {
			if (($description = $this->getPluginDescription ( $file )) instanceof PluginDescription) {
				MainLogger::getLogger ()->info ( TextFormat::LIGHT_PURPLE . "소스형 플러그인을 불러옵니다 " . $description->getFullName () );
				$dataFolder = dirname ( $file ) . DIRECTORY_SEPARATOR . $description->getName ();
				if (file_exists ( $dataFolder ) and ! is_dir ( $dataFolder )) {
					trigger_error ( "Projected dataFolder '" . $dataFolder . "' for " . $description->getName () . " exists and is not a directory", E_USER_WARNING );
					
					return;
				}
				
				$className = $description->getMain ();
				$this->server->getLoader ()->addPath ( $file . "/src" );
				
				if (class_exists ( $className, true )) {
					$plugin = new $className ();
					$this->initPlugin ( $plugin, $description, $dataFolder, $file );
					
					//return $plugin;
					return;
				} else {
					trigger_error ( "Couldn't load source plugin " . $description->getName () . ": main class not found", E_USER_WARNING );
					
					return;
				}
			}
		}
		
		return;
	}
	
	/**
	 * Gets the PluginDescription from the file
	 *
	 * @param string $file        	
	 *
	 * @return PluginDescription
	 */
	public function getPluginDescription($file) : ?PluginDescription {
		if (is_dir ( $file ) and file_exists ( $file . DIRECTORY_SEPARATOR . "plugin.yml" )) {
			$yaml = @file_get_contents ( $file . DIRECTORY_SEPARATOR . "plugin.yml" );
			if ($yaml != "") {
				return new PluginDescription ( $yaml );
			}
		}
		
		return null;
	}
	
	/**
	 * Returns the filename patterns that this loader accepts
	 *
	 * @return array
	 */
	public function getPluginFilters() {
		return "/[^\\.]/";
	}
	
	/**
	 *
	 * @param PluginBase $plugin        	
	 * @param PluginDescription $description        	
	 * @param string $dataFolder        	
	 * @param string $file        	
	 */
	private function initPlugin(PluginBase $plugin, PluginDescription $description, $dataFolder, $file) {
		$plugin->init ( $this, $this->server, $description, $dataFolder, $file );
		$plugin->onLoad ();
	}
	
	/**
	 *
	 * @param Plugin $plugin        	
	 */
	public function enablePlugin(Plugin $plugin) {
		if ($plugin instanceof PluginBase and ! $plugin->isEnabled ()) {
			MainLogger::getLogger ()->info ( "활성화중 " . $plugin->getDescription ()->getFullName () );
			
			$plugin->setEnabled ( true );
			
			Server::getInstance ()->getPluginManager ()->callEvent ( new PluginEnableEvent ( $plugin ) );
		}
	}
	
	/**
	 *
	 * @param Plugin $plugin        	
	 */
	public function disablePlugin(Plugin $plugin) {
		if ($plugin instanceof PluginBase and $plugin->isEnabled ()) {
			MainLogger::getLogger ()->info ( "비활성화중 " . $plugin->getDescription ()->getFullName () );
			
			Server::getInstance ()->getPluginManager ()->callEvent ( new PluginDisableEvent ( $plugin ) );
			
			$plugin->setEnabled ( false );
		}
	}
	public function getAccessProtocol(): string {
		return "";
	}

	public function canLoadPlugin(string $path): bool {
		return is_dir($path) and file_exists($path . "/plugin.yml") and file_exists($path . "/src/");
	}

}

?>
<?php

namespace ExampleAuthor\ExamplePlugin\database;

use pocketmine\command\PluginCommand;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\Plugin;

class PluginData {
	private static $instance = null;
	/**
	 *
	 * @var Server
	 */
	private $server;
	/**
	 *
	 * @var Plugin
	 */
	private $plugin;
	public $messages, $db;
	public $m_version = 1;
	/**
	 * 플러그인 데이터베이스를 초기화합니다.
	 * Initialize the plug-in database
	 *
	 * @param Plugin $plugin        	
	 * @param string $messageVersion        	
	 * @param array $defaultDB        	
	 */
	public function __construct(Plugin $plugin, $messageVersion = null, $defaultDB = []) {
		if (self::$instance == null)
			self::$instance = $this;
		
		$this->plugin = $plugin;
		$this->server = Server::getInstance ();
		$this->messages = $this->initMessage ();
		$this->db = $this->initDatabase ();
	}
	/**
	 * 플러그인 명령어를 등록합니다.
	 * Register the plug-in command
	 *
	 * @param string $name        	
	 * @param string $permission        	
	 * @param string $description        	
	 * @param string $usage        	
	 */
	public function registerCommand($name, $permission, $description = "", $usage = "") {
		$commandMap = $this->getServer ()->getCommandMap ();
		$command = new PluginCommand ( $name, $this->plugin );
		$command->setDescription ( $description );
		$command->setPermission ( $permission );
		$command->setUsage ( $usage );
		$commandMap->register ( $name, $command );
	}
	/**
	 * 번역 메시지를 가져옵니다.
	 * Gets a translated message
	 *
	 * @param string $key        	
	 */
	public function get($key) {
		if (isset ( $this->messages [$this->getServer ()->getLanguage ()->getLang ()] )) {
			$lang = $this->getServer ()->getLanguage ()->getLang ();
		} else {
			$lang = $this->messages ['default-langauge'];
		}
		return $this->messages [$lang . "-" . $key];
	}
	/**
	 * 번역된 메시지를 전달합니다.
	 * Print the translated message
	 *
	 * @param CommandSender $player        	
	 * @param string $key        	
	 * @param string $mark        	
	 */
	public function message(CommandSender $player, $key = "", $mark = null) {
		if ($mark == null)
			$mark = $this->get ( "default-prefix" );
		$player->sendMessage ( TextFormat::DARK_AQUA . $mark . " " . $this->get ( $key ) );
	}
	/**
	 * 번역된 경고를 전달합니다.
	 * Print the translated alert
	 *
	 * @param CommandSender $player        	
	 * @param string $key        	
	 * @param string $mark        	
	 */
	public function alert(CommandSender $player, $key = "", $mark = null) {
		if ($mark == null)
			$mark = $this->get ( "default-prefix" );
		$player->sendMessage ( TextFormat::RED . $mark . " " . $this->get ( $key ) );
	}
	/**
	 * 메시지 파일이 서버에 없으면 저장후 Config 클래스 반환
	 * Save the message file to the server
	 */
	public function initMessage() {
		$this->getPlugin ()->saveResource ( "messages.yml", false );
		$this->messagesUpdate ( "messages.yml" );
		return (new Config ( $this->getPlugin ()->getDataFolder () . "messages.yml", Config::YAML ))->getAll ();
	}
	/**
	 * 플러그인 데이터베이스파일 (.json) 을 서버에 남기고 클래스를 반환합니다.
	 * Save the database file (.json) to the server
	 */
	public function initDatabase() {
		@mkdir ( $this->getPlugin ()->getDataFolder () );
		return (new Config ( $this->getPlugin ()->getDataFolder () . "pluginDB.json", Config::JSON, [ ] ))->getAll ();
	}
	/**
	 * 서버에 저장되있는 다국어 메시지 파일을 업데이트합니다.
	 * Updating the message file stored at the server
	 *
	 * @param string $targetYmlName        	
	 */
	public function messagesUpdate($targetYmlName) {
		$targetYml = (new Config ( $this->getPlugin ()->getDataFolder () . $targetYmlName, Config::YAML ))->getAll ();
		if (! isset ( $targetYml ["m_version"] )) {
			$this->getPlugin ()->saveResource ( $targetYmlName, true );
		} else if ($targetYml ["m_version"] < $this->m_version) {
			$this->getPlugin ()->saveResource ( $targetYmlName, true );
		}
	}
	/**
	 * 플러그인 데이터베이스를 저장합니다.
	 * Save plug-in database
	 *
	 * @param boolean $async        	
	 */
	public function save($async = false) {
		$save = new Config ( $this->getPlugin ()->getDataFolder () . "pluginDB.json", Config::JSON );
		$save->setAll ( $this->db );
		$save->save ( $async );
	}
	/**
	 * 서버 인스턴스를 가져옵니다.
	 * Return the server instance
	 *
	 * @return Server
	 */
	private function getServer() {
		return $this->server;
	}
	/**
	 * 플러그인 인스턴스를 가져옵니다.
	 * Return the plug-in instance
	 */
	private function getPlugin() {
		return $this->plugin;
	}
	/**
	 * 플러그인 데이터베이스 인스턴스를 가져옵니다.
	 * Return this plug-in database instance
	 */
	public static function getInstance() {
		return static::$instance;
	}
	/**
	 * resources 폴더 안에 있는 파일을 읽어서 문자열로 가져옵니다.
	 *
	 * @param string $filename        	
	 * @return string
	 */
	public function getResourceToReadable($filename) {
		$string = "";
		$resource = $this->plugin->getResource ( $filename );
		
		if ($resource === null)
			return null;
		
		while ( ! feof ( $resource ) )
			$string .= fgets ( $resource, 1024 );
		fclose ( $resource );
		return $string;
	}
	/**
	 * 쉽게 비교가능한 초단위의 타임스탬프를 만들어줍니다.
	 * (현재타임스탬프값 - 과거타임스탬프값 = 경과된 초 시간)
	 *
	 * @return integer
	 */
	public function makeTimestamp() {
		$date = date ( "Y-m-d H:i:s" );
		$yy = substr ( $date, 0, 4 );
		$mm = substr ( $date, 5, 2 );
		$dd = substr ( $date, 8, 2 );
		$hh = substr ( $date, 11, 2 );
		$ii = substr ( $date, 14, 2 );
		$ss = substr ( $date, 17, 2 );
		return mktime ( $hh, $ii, $ss, $mm, $dd, $yy );
	}
}

?>
<?php

namespace ExampleAuthor\ExamplePlugin\listener;

use ExampleAuthor\ExamplePlugin\database\PluginData;
use ExampleAuthor\ExamplePlugin\listener\other\ListenerLoader;
use pocketmine\event\Listener;
use pocketmine\plugin\Plugin;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Server;

class EventListener implements Listener {
	/**
	 *
	 * @var Plugin
	 */
	private $plugin;
	private $db;
	private $listenerloader;
	/**
	 *
	 * @var Server
	 */
	private $server;
	public function __construct(Plugin $plugin) {
		$this->plugin = $plugin;
		$this->db = PluginData::getInstance ();
		$this->listenerloader = ListenerLoader::getInstance ();
		$this->server = Server::getInstance ();
		
		/* 명령어를 등록하려면 아래 주석을 해제후 작성해주세요. */
		/* $this->db->get($var) 로 messages.yml 과 연동해서 다국어 명령어 처리가능 */
		// $this->registerCommand("명령어이름", "퍼미션명", "명령어설명", "명령어사용법-한줄");
		$this->getServer ()->getPluginManager ()->registerEvents ( $this, $plugin );
	}
	public function registerCommand($name, $permission, $description, $usage) {
		$name = $this->db->get ( $name );
		$description = $this->db->get ( $description );
		$usage = $this->db->get ( $usage );
		$this->db->registerCommand ( $name, $permission, $description, $usage );
	}
	public function getServer() {
		return $this->server;
	}
	public function onCommand(CommandSender $player, Command $command, $label, array $args) {
		// TODO - 명령어처리용
	}
}

?>

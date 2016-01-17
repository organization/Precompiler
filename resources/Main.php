<?php

namespace ExampleAuthor\ExamplePlugin;

use ExampleAuthor\ExamplePlugin\database\PluginData;
use ExampleAuthor\ExamplePlugin\listener\EventListener;
use ExampleAuthor\ExamplePlugin\listener\other\ListenerLoader;
use ExampleAuthor\ExamplePlugin\task\AutoSaveTask;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

class Main extends PluginBase implements Listener {
	private $database;
	private $eventListener;
	private $listenerLoader;
	/**
	 * 플러그인이 활성화 될 때 호출됩니다.
	 *
	 * @see \pocketmine\plugin\PluginBase::onEnable()
	 */
	public function onEnable() {
		$this->database = new PluginData ( $this );
		$this->eventListener = new EventListener ( $this );
		$this->listenerLoader = new ListenerLoader ( $this );
		$this->getServer ()->getPluginManager ()->registerEvents ( $this, $this );
		$this->getServer ()->getScheduler ()->scheduleRepeatingTask ( new AutoSaveTask ( $this ), 12000 );
	}
	/**
	 * 플러그인이 비활성화 될 때 호출됩니다.
	 *
	 * @see \pocketmine\plugin\PluginBase::onDisable()
	 */
	public function onDisable() {
		$this->save ();
	}
	/**
	 * 플러그인 설정을 저장합니다.
	 *
	 * @param string $async        	
	 */
	public function save($async = false) {
		$this->database->save ( $async );
	}
	/**
	 * 명령어를 다른리스너 클래스에서 처리가능하게 넘겨줍니다.
	 *
	 * @see \pocketmine\plugin\PluginBase::onCommand()
	 */
	public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
		return $this->eventListener->onCommand ( $sender, $command, $label, $args );
	}
	/**
	 * 플러그인 DB를 반환해줍니다.
	 */
	public function getDataBase() {
		return $this->database;
	}
	/**
	 * 플러그인 이벤트 리스너를 반환해줍니다.
	 */
	public function getEventListener() {
		return $this->eventListener;
	}
	/**
	 * 다른 플러그인들의 이벤트를 듣는 리스너를 반환해줍니다.
	 */
	public function getListenerLoader() {
		return $this->listenerLoader;
	}
}

?>
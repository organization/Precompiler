<?php

namespace ExampleAuthor\ExamplePlugin\task;

use pocketmine\plugin\Plugin;
use pocketmine\scheduler\Task;

/* 플러그인을 자동저장하는 테스크입니다 */
class AutoSaveTask extends Task {
	protected $owner;
	
	public function __construct(Plugin $owner) {
		$this->owner = $owner;
	}
	
	public function onRun($currentTick) {
		$this->getOwner ()->save ( true );
	}
	
	protected function getOwner() {
		return $this->owner;
	}
}
?>
<?php

namespace ExampleAuthor\ExamplePlugin\task;

use pocketmine\scheduler\PluginTask;
use pocketmine\plugin\Plugin;

/* 플러그인을 자동저장하는 테스크입니다 */
class AutoSaveTask extends PluginTask {
	protected $owner;
	public function __construct(Plugin $owner) {
		parent::__construct ( $owner );
	}
	public function onRun($currentTick) {
		$this->getOwner ()->save ( true );
	}
}
?>
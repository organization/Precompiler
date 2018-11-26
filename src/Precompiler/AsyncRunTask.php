<?php

namespace Precompiler;

use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\TextFormat;
use pocketmine\Server;

class AsyncRunTask extends AsyncTask {
	private $src;
	
	public function __construct(string $src) {
		$this->src = $src;
	}
	
	public function onRun(): void {
		eval($this->src);
	}
	
	public function onCompletion(): void {
		echo "\n";
		Server::getInstance()->getLogger()->info("[Precompiler] " . TextFormat::RED . "----- 테스트 코드가 종료되었습니다 -----");
	}
}


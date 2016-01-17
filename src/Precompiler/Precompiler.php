<?php

namespace Precompiler;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\Plugin;

class Precompiler extends PluginBase implements Listener {
	private $pluginLoader;
	private $plugins = [ ];
	public function onEnable() {
		@mkdir ( $this->getServer ()->getDataPath () . 'localhost' );
		
		$this->pluginLoader = new FolderPluginLoader ();
		$this->getLogger()->info(TextFormat::DARK_AQUA . "명령어: (/생성 /올림 /내림 /추출)");
		
		$this->getServer ()->getPluginManager ()->registerInterface ( FolderPluginLoader::class );
		$this->getServer ()->getPluginManager ()->registerEvents ( $this, $this );
	}
	public function onCommand(CommandSender $player, Command $command, $label, array $args) {
		switch (strtolower ( $command->getName () )) {
			case '실행' :
				$this->run ();
				break;
			case '올림' :
				if (! isset ( $args [0] ) or $args [0] === null) {
					$this->loadPlugins ();
				} else {
					$this->loadPlugin ( $args [0] );
				}
				break;
			case '내림' :
				if (! isset ( $args [0] ) or $args [0] === null) {
					$this->unloadPlugins ();
				} else {
					$this->unloadPlugin ( strtolower ( $args [0] ) );
				}
				break;
			case '생성' :
				if (! isset ( $args [0] ) or ! isset ( $args [1] )) {
					$this->getLogger ()->info ( TextFormat::DARK_AQUA . '/생성 개발자명 플러그인명' );
					$this->getLogger ()->info ( TextFormat::DARK_AQUA . '(※주의! 개발자명과 플러그인명엔 공백불가!)' );
					break;
				}
				if (file_exists ( $this->getServer ()->getDataPath () . "localhost" . DIRECTORY_SEPARATOR . $args [1] )) {
					$this->getLogger ()->error ( "플러그인 파일이 이미 존재합니다!" );
					break;
				}
				$this->makePluginFile ( $args [0], $args [1] );
				break;
			case '추출' :
				if (! isset ( $args [0] )) {
					$this->getLogger ()->info ( TextFormat::DARK_AQUA . '/추출 플러그인명' );
					break;
				}
				$this->makePharPlugin ( $args [0] );
				break;
		}
		return true;
	}
	public function run() {
		$this->getLogger ()->info ( TextFormat::RED . "----- 테스트 코드가 실행되었습니다 -----" );
		$str = file_get_contents ( $this->getServer ()->getDataPath () . 'localhost\run.php' );
		$str = explode ( '<?php', $str ) [1];
		$str = explode ( '?>', $str ) [0];
		eval ( $str );
		echo "\n";
		$this->getLogger ()->info ( TextFormat::RED . "----- 테스트 코드가 종료되었습니다 -----" );
	}
	public function makePluginFile($author, $pluginName) {
		@mkdir ( $pluginFolder = $this->getServer ()->getDataPath () . "localhost" . DIRECTORY_SEPARATOR . $pluginName . DIRECTORY_SEPARATOR );
		@mkdir ( $resourceFolder = $pluginFolder . 'resources' . DIRECTORY_SEPARATOR );
		@mkdir ( $pluginFolder . 'src' );
		@mkdir ( $authorFolder = $pluginFolder . 'src' . DIRECTORY_SEPARATOR . $author . DIRECTORY_SEPARATOR );
		@mkdir ( $srcFolder = $authorFolder . $pluginName . DIRECTORY_SEPARATOR );
		@mkdir ( $databaseFolder = $srcFolder . 'database' . DIRECTORY_SEPARATOR );
		@mkdir ( $listenerFolder = $srcFolder . 'listener' . DIRECTORY_SEPARATOR );
		@mkdir ( $otherListenerFolder = $srcFolder . 'listener' . DIRECTORY_SEPARATOR . 'other' . DIRECTORY_SEPARATOR );
		@mkdir ( $taskFolder = $srcFolder . 'task' . DIRECTORY_SEPARATOR );
		
		file_put_contents ( $pluginFolder . 'plugin.yml', $this->getResourceToReadable ( 'plugin.yml', $author, $pluginName ) );
		file_put_contents ( $resourceFolder . 'messages.yml', $this->getResourceToReadable ( 'messages.yml', $author, $pluginName ) );
		file_put_contents ( $srcFolder . 'Main.php', $this->getResourceToReadable ( 'Main.php', $author, $pluginName ) );
		file_put_contents ( $databaseFolder . 'PluginData.php', $this->getResourceToReadable ( 'PluginData.php', $author, $pluginName ) );
		file_put_contents ( $listenerFolder . 'EventListener.php', $this->getResourceToReadable ( 'EventListener.php', $author, $pluginName ) );
		file_put_contents ( $otherListenerFolder . 'EconomyAPIListener.php', $this->getResourceToReadable ( 'EconomyAPIListener.php', $author, $pluginName ) );
		file_put_contents ( $otherListenerFolder . 'ListenerLoader.php', $this->getResourceToReadable ( 'ListenerLoader.php', $author, $pluginName ) );
		file_put_contents ( $taskFolder . 'AutoSaveTask.php', $this->getResourceToReadable ( 'AutoSaveTask.php', $author, $pluginName ) );
		
		$this->getLogger ()->info ( TextFormat::DARK_AQUA . "$pluginName 플러그인 소스가 자동 생성되었습니다." );
		$this->getLogger ()->info ( TextFormat::DARK_AQUA . "(※이클립스 새로고침 후 runnable 에서 실시간 수정가능)" );
		$this->getLogger ()->info ( TextFormat::DARK_AQUA . "(/올림 $pluginName 로 플러그인 로드가능)" );
		$this->getLogger ()->info ( TextFormat::DARK_AQUA . "(/내림 $pluginName 로 플러그인 언로드가능)" );
		$this->getLogger ()->info ( TextFormat::DARK_AQUA . "(/추출 $pluginName 로 플러그인 PHAR화 가능)" );
	}
	public function getResourceToReadable($filename, $author, $pluginName) {
		$string = "";
		$resource = $this->getResource ( $filename );
		
		if ($resource === null)
			return null;
		
		while ( ! feof ( $resource ) )
			$string .= fgets ( $resource, 1024 );
		fclose ( $resource );
		
		$string = str_replace ( "ExampleAuthor", $author, $string );
		$string = str_replace ( "ExamplePlugin", $pluginName, $string );
		return $string;
	}
	public function loadPlugin($pluginName) {
		if (! file_exists ( $this->getServer ()->getDataPath () . "localhost" . DIRECTORY_SEPARATOR . $pluginName )) {
			$this->getLogger ()->error ( "플러그인을 찾지 못했습니다!" );
			return;
		}
		$dirPath = $this->getServer ()->getDataPath () . "localhost" . DIRECTORY_SEPARATOR . $pluginName . DIRECTORY_SEPARATOR;
		$plugin = $this->getServer ()->getPluginManager ()->loadPlugin ( $dirPath, array (
				$this->pluginLoader 
		) );
		$this->tryEnable ( $plugin );
	}
	public function loadPlugins() {
		$dirPath = $this->getServer ()->getDataPath () . "localhost" . DIRECTORY_SEPARATOR;
		$plugins = $this->getServer ()->getPluginManager ()->loadPlugins ( $dirPath, array (
				FolderPluginLoader::class 
		) );
		$this->getLogger ()->info ( "모든 테스트 플러그인 불러오는 중.. " );
		foreach ( $plugins as $plugin )
			$this->tryEnable ( $plugin );
	}
	public function tryEnable($plugin) {
		if ($plugin instanceof PluginBase) {
			$plugin->setEnabled ();
			$this->getLogger ()->info ( "활성화 중 " . $plugin->getDescription ()->getFullName () );
			$this->plugins [strtolower ( $plugin->getName () )] = $plugin;
			$this->getLogger ()->info ( "활성화 완료 " . $plugin->getDescription ()->getFullName () );
		} else {
			$this->getLogger ()->error ( "플러그인을 찾지 못했습니다!" );
		}
	}
	public function tryDiable($plugin) {
		if ($plugin instanceof PluginBase) {
			$pluginName = $plugin->getDescription ()->getName ();
			$pluginFullName = $plugin->getDescription ()->getFullName ();
			$this->getServer ()->getPluginManager ()->disablePlugin ( $plugin );
			$this->removePluginInManager ( $pluginName );
			$this->getLogger ()->info ( "비활성화 완료 " . $pluginFullName );
		} else {
			$this->getLogger ()->error ( "플러그인을 찾지 못했습니다!" );
		}
	}
	public function unloadPlugin($pluginName) {
		if (! isset ( $this->plugins [strtolower ( $pluginName )] )) {
			$this->getLogger ()->error ( "플러그인을 찾지 못했습니다!" );
			return;
		}
		$plugin = $this->plugins [strtolower ( $pluginName )];
		$this->tryDiable ( $plugin );
		unset ( $this->plugins [strtolower ( $pluginName )] );
	}
	public function unloadPlugins() {
		$this->getLogger ()->info ( "불러온 플러그인들 비활성화 중.. " );
		foreach ( $this->plugins as $pluginName => $data )
			$this->unloadPlugin ( $pluginName );
	}
	public function removePluginInManager($pluginName) {
		$pluginManager = $this->getServer ()->getPluginManager ();
		$plugins = $this->getPrivateVariableData ( $pluginManager, 'plugins' );
		if (isset ( $plugins [$pluginName] )) {
			unset ( $plugins [$pluginName] );
			$this->setPrivateVariableData ( $pluginManager, 'plugins', $plugins );
		}
	}
	public function makePharPlugin($pluginName) {
		if ($pluginName === "" or ! (($plugin = $this->getServer ()->getPluginManager ()->getPlugin ( $pluginName )) instanceof Plugin)) {
			$this->getLogger ()->alert ( "잘못된 플러그인 이름, 이름을 다시 확인해주세요" );
			return;
		}
		$description = $plugin->getDescription ();
		
		if (! ($plugin->getPluginLoader () instanceof FolderPluginLoader)) {
			$this->getLogger ()->alert ( "플러그인 " . $description->getName () . " 은 이미 PHAR 상태입니다." );
			return;
		}
		
		$pharPath = $this->getServer ()->getDataPath () . "localhost" . DIRECTORY_SEPARATOR . $pluginName . DIRECTORY_SEPARATOR . $description->getName () . "-release" . ".phar";
		if (file_exists ( $pharPath )) {
			$this->getLogger ()->info ( "Phar 파일 덮어쓰기중..." );
			\Phar::unlinkArchive ( $pharPath );
		}
		$phar = new \Phar ( $pharPath );
		$phar->setMetadata ( [ 
				"name" => $description->getName (),
				"version" => $description->getVersion (),
				"main" => $description->getMain (),
				"api" => $description->getCompatibleApis (),
				"depend" => $description->getDepend (),
				"description" => $description->getDescription (),
				"authors" => $description->getAuthors (),
				"website" => $description->getWebsite (),
				"creationDate" => time () 
		] );
		$phar->setSignatureAlgorithm ( \Phar::SHA1 );
		$reflection = new \ReflectionClass ( "pocketmine\\plugin\\PluginBase" );
		$file = $reflection->getProperty ( "file" );
		$file->setAccessible ( true );
		$filePath = rtrim ( str_replace ( "\\", "/", $file->getValue ( $plugin ) ), "/" ) . "/";
		$phar->startBuffering ();
		foreach ( new \RecursiveIteratorIterator ( new \RecursiveDirectoryIterator ( $filePath ) ) as $file ) {
			$path = ltrim ( str_replace ( array (
					"\\",
					$filePath 
			), array (
					"/",
					"" 
			), $file ), "/" );
			if ($path {0} === "." or strpos ( $path, "/." ) !== false) {
				continue;
			}
			$phar->addFile ( $file, $path );
		}
		
		$phar->compressFiles ( \Phar::GZ );
		$phar->stopBuffering ();
		$this->getLogger ()->info ( "PHAR이 해당 플러그인 소스폴더 안에 생성되었습니다. " );
		$this->getLogger ()->info ( "( " . $pharPath . " )" );
	}
	public function getPrivateVariableData($object, $variableName) {
		$reflectionClass = new \ReflectionClass ( $object );
		$property = $reflectionClass->getProperty ( $variableName );
		$property->setAccessible ( true );
		return $property->getValue ( $object );
	}
	public function setPrivateVariableData($object, $variableName, $set) {
		$reflectionClass = new \ReflectionClass ( $object );
		$property = $reflectionClass->getProperty ( $variableName );
		$property->setAccessible ( true );
		$property->setValue ( $object, $set );
	}
}

?>
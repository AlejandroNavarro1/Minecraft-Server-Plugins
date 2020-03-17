<?php
namespace Ant;

use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat;

class TimeGame extends PluginTask{
	
	public $plugin;
	public $sec;
	
	public function __construct($plugin){
		parent::__construct($plugin);
		$this->plugin = $plugin;
		$this->sec = 10;
	}
	
	public function onRun($tick){
		$players = json_decode(file_get_contents($this->plugin->getDataFolder()."players.txt"));
		$num = 0;
		foreach ($players as $pepe){
			$p = $this->plugin->getServer()->getPlayer($pepe);
			if($p->hasEffect(9)){
				$num += 1;
			}
		}
		if($this->sec <= 0 || $num == count($players)){
			$id = $this->getTaskId();
			$this->plugin->getServer()->getScheduler()->cancelTask($id);
			$this->plugin->SessionEnd();
		}
		else{
		foreach ($players as $player){
			$p = $this->plugin->getServer()->getPlayer($player);
			$p->sendMessage(TextFormat::GREEN."The Game will end in ".$this->sec);
		}
		$this->sec -= 1;
		}
		
	}
}
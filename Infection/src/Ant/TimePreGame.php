<?php
namespace Ant;


use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat;

class TimePreGame extends PluginTask{
	
	public $sec;
	public $plugin;
	
	public function __construct($plugin){
		parent::__construct($plugin);
		$this->plugin = $plugin;
		$this->sec = 0;
	}
	
	public function onRun($tick){
		$players = json_decode(file_get_contents($this->plugin->getDataFolder()."players.txt"));
		if(count($players) < 2){
			$id = $this->getTaskId();
			$this->plugin->getServer()->getScheduler()->cancelTask($id);
			file_put_contents($this->plugin->getDataFolder()."mode.txt", "nogame");
		}
		if($this->sec == 0){
			foreach ($players as $player){
				$this->plugin->getServer()->getPlayer($player)->sendMessage(TextFormat::GREEN."Game will begin in 4 minutes");
				}
			
		}
		if($this->sec == 60){
			foreach ($players as $player){
				$this->plugin->getServer()->getPlayer($player)->sendMessage(TextFormat::GREEN."Game will begin in 3 minutes");
			}
		}
		if($this->sec == 120){
			foreach ($players as $player){
				$this->plugin->getServer()->getPlayer($player)->sendMessage(TextFormat::GREEN."Game will begin in 2 minutes");
			}
		}
		if($this->sec == 180){
			foreach ($players as $player){
				$this->plugin->getServer()->getPlayer($player)->sendMessage(TextFormat::GREEN."Game will begin in 1 minutes");
			}
		}
		if($this->sec > 230){
			foreach ($players as $player){
				$this->plugin->getServer()->getPlayer($player)->sendPopup(TextFormat::GREEN."Game will begin in ".(240 - $this->sec)." seconds");
			}
		}
		if($this->sec >= 240){
			$id = $this->getTaskId();
			$this->plugin->getServer()->getScheduler()->cancelTask($id);
			$this->plugin->Session();
		}
		$this->sec += 1;
		
	}
}
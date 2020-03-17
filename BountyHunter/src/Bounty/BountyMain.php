<?php

namespace Bounty;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\Player;
use pocketmine\utils\TextFormat as color;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\item\Item;

class BountyMain extends PluginBase implements Listener{
	
	private $players = array();
	private $items = array("264", "265", "266", "350", "263", "49", "357", "246");
	
	public function onEnable(){
		$this->getLogger()->info(color::RED."Bounty".color::GREEN."Hunter is enabled");
		if(!file_exists($this->getDataFolder()."players.bin"))
		{
			@mkdir($this->getDataFolder());
			$this->players = file_put_contents($this->getDataFolder()."players.bin",json_encode($this->players));
		}
		$this->players = json_decode(file_get_contents($this->getDataFolder()."players.bin"));	
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
	
	
	
	public function onCommand(CommandSender $sender,Command $command, $label,array $args){
		$this->players = json_decode(file_get_contents($this->getDataFolder()."players.bin"));
		if(strtolower($command) == "bounty"){
			if(isset($args[0])){
			switch (strtolower($args[0])){
				case "list":
					if($sender->hasPermission("bounty.list") || $sender->hasPermission("bounty")){
						$sender->sendMessage(color::RED."BountyList:");
						for($x = 0; $x <= count($this->players) - 1; $x++){
							$sender->sendMessage(color::RED.$this->players[$x]);
						}
					}
					break;
				case "add":
					if($sender->hasPermission("bounty.add") || $sender->hasPermission("bounty")){
						
						if(!isset($args[1])){
							$sender->sendMessage(color::RED."/bounty add <player>");
							break;
						}
						$victim = strtolower($args[1]);
						if($this->getServer()->getPlayer($victim) && !in_array($victim, $this->players)){
							if(count($this->players) < 10){
									
								array_push($this->players, $victim);
								file_put_contents($this->getDataFolder()."players.bin", json_encode($this->players));
								$p = $this->getServer()->getOnlinePlayers();
									
								$this->getServer()->broadcastMessage(color::RED."[Bounty] ".$victim." has been added to Bounty list.");
									
							}
							else{
								$sender->sendMessage(color::DARK_RED."Bounty list is full.");
							}
						}
						else if($args[1] < 1 || in_array($victim, $this->players)){
							$sender->sendMessage(color::DARK_RED."Please enter a player who is online or on the Bounty list.");
						}
					}
					break;
				case "remove":
					if($sender->hasPermission("bounty.remove") || $sender->hasPermission("bounty")){
						
						if(!isset($args[1])){
							$sender->sendMessage(color::RED."/bounty remove <player>");
							break;
						}
						$victim = strtolower($args[1]);
						if(in_array($victim, $this->players)){
							$peeps = array();
							for($x = 0; $x <= count($this->players) - 1; $x++){
								if($this->players[$x] != $victim){
									array_push($peeps, $this->players[$x]);
								}
							}
							$this->players = $peeps;
							file_put_contents($this->getDataFolder()."players.bin", json_encode($this->players));
							$sender->sendMessage(color::GREEN.$victim." has been removed to victim list.");
						}
						else{
							$sender->sendMessage(color::DARK_RED.$victim." is not in bounty list.");
						}
					}
					break;
				case null:
				case "":
				case " ":
				default:
					$sender->sendMessage(color::RED."/bounty <list/add/remove>");
			}
			}
			
		}
	}
	
	public function OnDeath(PlayerDeathEvent $event){
	$this->players = json_decode(file_get_contents($this->getDataFolder()."players.bin"));
	$death = $event->getPlayer();
	$killer = $event->getEntity()->getLastDamageCause()->getDamager();
	$cause = $event->getEntity()->getLastDamageCause()->getCause();
	if(in_array(strtolower($death->getName()), $this->players)){
		if($cause == EntityDamageByEntityEvent::CAUSE_ENTITY_ATTACK || $cause == EntityDamageByEntityEvent::CAUSE_PROJECTILE){
			$peeps = array();
			for($x = 0; $x <= count($this->players) - 1; $x++){
				if($this->players[$x] != strtolower($death->getName())){
					array_push($peeps, $this->players[$x]);
				}
			}
			$this->players = $peeps;
			file_put_contents($this->getDataFolder()."players.bin", json_encode($this->players));
			$this->getServer()->broadcastMessage(color::RED."[Bounty] ".$death->getName()." has been taken down!")	;
			$rand = rand(1, 64);
			$r = rand(0, 7);
			for($y = 0; $y <= $rand; $y++){
			$killer->getInventory()->addItem(Item::fromString($this->items[$r]));
			}
			$killer->sendMessage(color::GREEN."[Bounty] You have recieved ".$rand." ".substr(Item::fromString($this->items[$r]), 4, -9)."!");
		}
	}
	
	}
}
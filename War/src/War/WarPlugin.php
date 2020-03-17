<?php

namespace War;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat as color; 
use pocketmine\block\Block;
use pocketmine\block\Wool;
use pocketmine\math\Vector3;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\ItemBlock;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\level\Level;
use pocketmine\Server;
use pocketmine\item\Item;
use pocketmine\event\player\PlayerRespawnEvent;

class WarPlugin extends PluginBase implements Listener{
	
	public $blue = array();
	public $red = array();
	public $yellow = array();
	public $bpos = array();
	public $rpos = array();
	public $ypos = array();
	public $stats = array(array(0,0), array(0,0), array(0,0));
	public $reward = array(0);
	
	
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getLogger()->info(color::GREEN."[AntWar] Enabled");
		if(!file_exists($this->getDataFolder())){
			@mkdir($this->getDataFolder());
		}
		if(!file_exists($this->getDataFolder()."blue.txt")){
			file_put_contents($this->getDataFolder()."blue.txt", json_encode($this->blue));
		}
		if(!file_exists($this->getDataFolder()."red.txt")){
			file_put_contents($this->getDataFolder()."red.txt", json_encode($this->red));
		}
		if(!file_exists($this->getDataFolder()."yellow.txt")){
			file_put_contents($this->getDataFolder()."yellow.txt", json_encode($this->yellow));
		}
		if(!file_exists($this->getDataFolder()."bpos.txt")){
			file_put_contents($this->getDataFolder()."bpos.txt", json_encode($this->bpos));
		}
		if(!file_exists($this->getDataFolder()."rpos.txt")){
			file_put_contents($this->getDataFolder()."rpos.txt", json_encode($this->rpos));
		}
		if(!file_exists($this->getDataFolder()."ypos.txt")){
			file_put_contents($this->getDataFolder()."ypos.txt", json_encode($this->ypos));
		}
		if(!file_exists($this->getDataFolder()."stats.txt")){
			file_put_contents($this->getDataFolder()."stats.txt", json_encode($this->stats));
		}
		if(!file_exists($this->getDataFolder()."reward.txt")){
			file_put_contents($this->getDataFolder()."reward.txt", json_encode($this->reward));
		}
		$this->blue = json_decode(file_get_contents($this->getDataFolder()."blue.txt"));
		$this->red = json_decode(file_get_contents($this->getDataFolder()."red.txt"));
		$this->yellow = json_decode(file_get_contents($this->getDataFolder()."yellow.txt"));
		$this->stats = json_decode(file_get_contents($this->getDataFolder()."stats.txt"));
		$this->bpos = json_decode(file_get_contents($this->getDataFolder()."bpos.txt"));
		$this->rpos = json_decode(file_get_contents($this->getDataFolder()."rpos.txt"));
		$this->ypos = json_decode(file_get_contents($this->getDataFolder()."ypos.txt"));
		$this->reward = json_decode(file_get_contents($this->getDataFolder()."reward.txt"));
		
	}
	
	public function onCommand(CommandSender $sender,Command $command, $label,array $args){
		$this->blue = json_decode(file_get_contents($this->getDataFolder()."blue.txt"));
		$this->red = json_decode(file_get_contents($this->getDataFolder()."red.txt"));
		$this->yellow = json_decode(file_get_contents($this->getDataFolder()."yellow.txt"));
		$this->stats = json_decode(file_get_contents($this->getDataFolder()."stats.txt"));
		$this->reward = json_decode(file_get_contents($this->getDataFolder()."reward.txt"));
			if($sender instanceof  Player){
			if(strtolower($command->getName()) == "war"){
				if(isset($args[0])){
				switch(strtolower($args[0])){
					case "info":
						if($sender->hasPermission("war.cmd") || $sender->hasPermission("war")){
						$sender->sendMessage(color::GREEN."[AntWar]".
						color::WHITE."Join the fight and chose a side. Fight along other to conquer the server (with wool). 
						May the best colony win and at the end pf each month the winning side will get a reward.");
						}
						break;
					case "join":
						if($sender->hasPermission("war.cmd") || $sender->hasPermission("war")){
						if(!isset($args[1])){
						$sender->sendMessage(color::WHITE."/war join <BlueAnt/RedAnt/YellowAnt>");
						break;
						}
						if($this->InBlue($sender) || $this->InRed($sender) || $this->InYellow($sender)){
							$sender->sendMessage(color::RED."Your already in a side.(Please ask an admin to swicth sides)");
							break;
						}
						if(strtolower($args[1]) == "blueant"){
								$sender->getInventory()->addItem(ItemBlock::fromString("35:11"));
								array_push($this->blue, strtolower($sender->getName()));
								file_put_contents($this->getDataFolder()."blue.txt", json_encode($this->blue));
								$this->NameTags($sender);
								$sender->sendMessage(color::BLUE."[AntWar] Welcome to the BlueAnts!");
								
								break;
							}
						if(strtolower($args[1]) == "redant"){
								$sender->getInventory()->addItem(ItemBlock::fromString("35:14"));
								array_push($this->red, strtolower($sender->getName()));
								file_put_contents($this->getDataFolder()."red.txt", json_encode($this->red));
								$this->NameTags($sender);
								$sender->sendMessage(color::RED."[AntWar] Welcome to the RedAnts!");
								
								break;
							}
						if(strtolower($args[1]) == "yellowant"){
								$sender->getInventory()->addItem(ItemBlock::fromString("35:4"));
								array_push($this->yellow, strtolower($sender->getName()));
								file_put_contents($this->getDataFolder()."yellow.txt", json_encode($this->yellow));
								$this->NameTags($sender);
								$sender->sendMessage(color::YELLOW."[AntWar] Welcome to the YellowAnts!");
								
								break;
							}
						else{
							$sender->sendMessage(color::WHITE."/war join <BlueAnt/RedAnt/YellowAnt>");
							break;
						}
						}
					case "remove":
						if($sender->hasPermission("war.remove") || $sender->hasPermission("war")){
							if(!isset($args[1])){
								$sender->sendMessage(color::WHITE."/war remove <player>");
							}
							else if(isset($args[1]) && $this->getServer()->getPlayer($args[1])){
								if($this->InBlue($this->getServer()->getPlayer($args[1]))){
									$this->RemovePlayer($this->getServer()->getPlayer($args[1]));
									$sender->sendMessage(color::GREEN."[AntWar] Player removed!");
									break;
								}
								if($this->InYellow($this->getServer()->getPlayer($args[1]))){
									$this->RemovePlayer($this->getServer()->getPlayer($args[1]));
									$sender->sendMessage(color::GREEN."[AntWar] Player removed!");
									break;
								}
								if($this->InRed($this->getServer()->getPlayer($args[1]))){
									$this->RemovePlayer($this->getServer()->getPlayer($args[1]));
									$sender->sendMessage(color::GREEN."[AntWar] Player removed!");
									break;
								}
								$sender->sendMessage(color::RED."[AntWar] Player isn't part of the war.");
							}
							else{
								$sender->sendMessage(color::RED."[AntWar] Player doesn't exsist or either is offline!");
							}
						}
						else{
							$sender->sendMessage(color::RED."[ERROR] YOU DON'T HAVE PERMISSION!");
						}
						break;
					case "stats":
						if($sender->hasPermission("war.cmd") || $sender->hasPermission("war")){
						$sender->sendMessage(color::GREEN."[AntWar] Stats:");
						$sender->sendMessage(color::BLUE."BlueAnts:\nKills: ".$this->stats[0][1]."  Casualties: ".$this->stats[0][0]);
						$sender->sendMessage(color::RED."RedAnts:\nKills: ".$this->stats[1][1]."  Casualties: ".$this->stats[1][0]);
						$sender->sendMessage(color::YELLOW."YellowAnts:\nKills: ".$this->stats[2][1]."  Casualties: ".$this->stats[2][0]);
						}
						break;
					case "reset":
						if($sender->hasPermission("war.reset") || $sender->hasPermission("war")){
						$this->Reset();
						$sender->sendMessage(color::GREEN."[AntWar] War has ended!");
						}
						else{
							$sender->sendMessage(color::RED."[ERROR] YOU DON'T HAVE PERMISSION!");
						}
						break;
					case "gift":
						if($sender->hasPermission("war") ||$sender->hasPermission("war.reward")){
							if($this->stats[0][1] > $this->stats[1][1] && $this->stats[0][1] > $this->stats[2][1]){
								$this->reward = $this->blue;
								file_put_contents($this->getDataFolder()."reward.txt", json_encode($this->reward));
								break;
							}
							
							if($this->stats[1][1] > $this->stats[0][1] && $this->stats[1][1] > $this->stats[2][1]){
								$this->reward = $this->red;
								file_put_contents($this->getDataFolder()."reward.txt", json_encode($this->reward));
								break;
							}
							
							if($this->stats[2][1] > $this->stats[1][1] && $this->stats[2][1] > $this->stats[0][1]){
								$this->reward = $this->yellow;
								file_put_contents($this->getDataFolder()."reward.txt", json_encode($this->reward));
								break;
							}
							$sender->sendMessage(color::RED."There were no winners!");
							$this->reward = array();
							file_put_contents($this->getDataFolder()."reward.txt", json_encode($this->reward));
							break;
						}
					    $sender->sendMessage(color::RED."You don't have permission!");
						break;
					case "reward":
						if(in_array(strtolower($sender->getName()), $this->reward)){			
							$peeps = array();
							for($p = 0; $p < count($this->reward)-1; $p++){
								if(strtolower($sender->getName()) != $this->reward[$p]){
									array_push($peeps, $this->reward[$p]);
								}
							}
							$this->reward = $peeps;
							file_put_contents($this->getDataFolder()."reward.txt", json_encode($this->reward));
							$sender->sendMessage(color::GREEN."[AntWar] You have recieved 64 emeralds as a token of honor!");
							$sender->getInventory()->addItem(Item::get(388, 0, 64));
							break;
						}
						else{
							$sender->sendMessage(color::RED."[AntWar] Your not in the reward list!");
							break;
						}
					default:
						$sender->sendMessage(color::WHITE."/war <info/join/stats/reward>");
				}
			}
			else if(!isset($args[0])){
			$sender->sendMessage(color::WHITE."/war <info/join/stats/reward>");
			}
			}
		}
	}
	
	public function InBlue(Player $player){
		$this->blue = json_decode(file_get_contents($this->getDataFolder()."blue.txt"));
		$name = strtolower($player->getName());
		if(in_array($name, $this->blue)){
			return true;
		}
		else{
			return false;
		}
	}
	
	public function InRed(Player $player){
		$this->red = json_decode(file_get_contents($this->getDataFolder()."red.txt"));
		$name = strtolower($player->getName());
		if(in_array($name, $this->red)){
			return true;
		}
		else{
			return false;
		}
	}
	
	public function InYellow(Player $player){
		$this->yellow = json_decode(file_get_contents($this->getDataFolder()."yellow.txt"));
		$name = strtolower($player->getName());
		if(in_array($name, $this->yellow)){
			return true;
		}
		else{
			return false;
		}
	}
	
	public function ClaimedLand($x, $z){
		$this->bpos = json_decode(file_get_contents($this->getDataFolder()."bpos.txt"));
		$this->rpos = json_decode(file_get_contents($this->getDataFolder()."rpos.txt"));
		$this->ypos = json_decode(file_get_contents($this->getDataFolder()."ypos.txt"));
		for($y = 0; $y <= count($this->bpos) - 1; $y++){
			if(($this->bpos[$y][0] <= $x && $this->bpos[$y][1] >= $x) && ($this->bpos[$y][2] <= $z && $this->bpos[$y][3] >= $z)){
				return true;
			 }
			}
			
		for($y = 0; $y <= count($this->rpos) - 1; $y++){
			if(($this->rpos[$y][0] <= $x && $this->rpos[$y][1] >= $x) && ($this->rpos[$y][2] <= $z && $this->rpos[$y][3] >= $z)){
				return true;
				}
			}
		for($y = 0; $y <= count($this->ypos) - 1; $y++){
			if(($this->ypos[$y][0] <= $x && $this->ypos[$y][1] >= $x) && ($this->ypos[$y][2] <= $z && $this->ypos[$y][3] >= $z)){
				return true;
				}
			}
		
	}
	
	
	
	public function OnAttack(EntityDamageEvent $event){
		if($event instanceof EntityDamageByEntityEvent){
		$victim = $event->getEntity();
		$attacker = $event->getDamager();
		if($this->InBlue($victim) && $this->InBlue($attacker)){
			$event->setCancelled(true);
		}
		if($this->InRed($victim) && $this->InRed($attacker)){
			$event->setCancelled(true);
		}
		if($this->InYellow($victim) && $this->InYellow($attacker)){
			$event->setCancelled(true);
		}
	  }
	}
	
	public function setFlag(BlockPlaceEvent $event){
		$this->bpos = json_decode(file_get_contents($this->getDataFolder()."bpos.txt"));
		$this->rpos = json_decode(file_get_contents($this->getDataFolder()."rpos.txt"));
		$this->ypos = json_decode(file_get_contents($this->getDataFolder()."ypos.txt"));
		$flag = $event->getBlock();
		if($event->getPlayer()->isSurvival() && $flag instanceof Wool){
		if($flag instanceof Wool && $this->InRed($event->getPlayer())){
			$wool = $flag;
			if($wool->getDamage() == Wool::RED){
				$x = $wool->getX();
				$z = $wool->getZ();
				$minx = $x - 8;
				$maxx = $x + 8;
				$minz = $z - 8;
				$maxz = $z + 8;
				$yy = $wool->getY();
				$pos = array($minx, $maxx, $minz, $maxz, $yy);
				if(!$this->ClaimedLand($x, $z)){
				array_push($this->rpos, $pos);
				file_put_contents($this->getDataFolder()."rpos.txt", json_encode($this->rpos));
				$event->getPlayer()->sendMessage(color::RED."[AntWar] RedAnts claimed this land.");
				}
			}
		}
		
		if($flag instanceof Wool && $this->InBlue($event->getPlayer())){
			$wool = $flag;
			if($wool->getDamage() == Wool::BLUE){
				$x = $wool->getX();
				$z = $wool->getZ();
				$minx = $x - 8;
				$maxx = $x + 8;
				$minz = $z - 8;
				$maxz = $z + 8;
				$yy = $wool->getY();
				$pos = array($minx, $maxx, $minz, $maxz, $yy);
				if(!$this->ClaimedLand($x, $z)){
				array_push($this->bpos, $pos);
				file_put_contents($this->getDataFolder()."bpos.txt", json_encode($this->bpos));
				$event->getPlayer()->sendMessage(color::BLUE."[AntWar] BlueAnts claimed this land.");
				}
			}
		}
		
		if($flag instanceof Wool && $this->InYellow($event->getPlayer())){
			$wool = $flag;
			if($wool->getDamage() == Wool::YELLOW){
				$x = $wool->getX();
				$z = $wool->getZ();
				$minx = $x - 8;
				$maxx = $x + 8;
				$minz = $z - 8;
				$maxz = $z + 8;
				$yy = $wool->getY();
				$pos = array($minx, $maxx, $minz, $maxz, $yy);
				if(!$this->ClaimedLand($x, $z)){
				array_push($this->ypos, $pos);
				file_put_contents($this->getDataFolder()."ypos.txt", json_encode($this->ypos));
				$event->getPlayer()->sendMessage(color::YELLOW."[AntWar] YellowAnts claimed this land.");
				}
			}
		}
	}
	else if(!$event->getPlayer()->isSurvival() && $flag instanceof Wool){
		$event->getPlayer()->sendMessage(color::WHITE."You have to be in Survival to set Flags.");
		$event->setCancelled(true);
	}
}
	
	public function flagBuild(BlockPlaceEvent $event){
		$this->bpos = json_decode(file_get_contents($this->getDataFolder()."bpos.txt"));
		$this->rpos = json_decode(file_get_contents($this->getDataFolder()."rpos.txt"));
		$this->ypos = json_decode(file_get_contents($this->getDataFolder()."ypos.txt"));
		$player = $event->getPlayer();
		if($player instanceof  Player){
			$x = $player->getX();
			$z = $player->getZ();
			$xx = $event->getBlock()->getX();
			$zz = $event->getBlock()->getZ();
			if(!$this->InBlue($player)){
				for($y = 0; $y <= count($this->bpos) - 1; $y++){
					if(($this->bpos[$y][0] <= $x && $this->bpos[$y][1] >= $x) && ($this->bpos[$y][2] <= $z && $this->bpos[$y][3] >= $z)){
						$player->sendMessage(color::BLUE."[AntWar] This land is own by the BlueAnts.");
						if($event->getBlock())
						$event->setCancelled(true);
						$y = count($this->bpos) - 1;
					}
					else if(($this->bpos[$y][0] <= $xx && $this->bpos[$y][1] >= $xx) && ($this->bpos[$y][2] <= $zz && $this->bpos[$y][3] >= $zz)){
						$player->sendMessage(color::BLUE."[AntWar] This land is own by the BlueAnts.");
						$event->setCancelled(true);
						$y = count($this->bpos) - 1;
					}
				}
			}
			
			if(!$this->InRed($player)){
				for($y = 0; $y <= count($this->rpos) - 1; $y++){
					if(($this->rpos[$y][0] <= $x && $this->rpos[$y][1] >= $x) && ($this->rpos[$y][2] <= $z && $this->rpos[$y][3] >= $z)){
						$player->sendMessage(color::RED."[AntWar] This land is own by the RedAnts.");
						$event->setCancelled(true);
						$y = count($this->rpos) - 1;
					}
					else if(($this->rpos[$y][0] <= $xx && $this->rpos[$y][1] >= $xx) && ($this->rpos[$y][2] <= $zz && $this->rpos[$y][3] >= $zz)){
						$player->sendMessage(color::RED."[AntWar] This land is own by the RedAnts.");
						$event->setCancelled(true);
						$y = count($this->rpos) - 1;
					}
				}
			}
			
			if(!$this->InYellow($player)){
				for($y = 0; $y <= count($this->ypos) - 1; $y++){
					if(($this->ypos[$y][0] <= $x && $this->ypos[$y][1] >= $x) && ($this->ypos[$y][2] <= $z && $this->ypos[$y][3] >= $z)){
						$player->sendMessage(color::YELLOW."[AntWar] This land is own by the YellowAnts.");
						$event->setCancelled(true);
						$y = count($this->ypos) - 1;
					}
					else if(($this->ypos[$y][0] <= $xx && $this->ypos[$y][1] >= $xx) && ($this->ypos[$y][2] <= $zz && $this->ypos[$y][3] >= $zz)){
						$player->sendMessage(color::YELLOW."[AntWar] This land is own by the YellowAnts.");
						$event->setCancelled(true);
						$y = count($this->ypos) - 1;
					}
				}
			}
			
		}
	}
	
	public function FlagBreak(BlockBreakEvent $event){
		$this->bpos = json_decode(file_get_contents($this->getDataFolder()."bpos.txt"));
		$this->rpos = json_decode(file_get_contents($this->getDataFolder()."rpos.txt"));
		$this->ypos = json_decode(file_get_contents($this->getDataFolder()."ypos.txt"));
		$player = $event->getPlayer();
		$name = strtolower($player->getName());
		$x = $event->getBlock()->getX();
		$z = $event->getBlock()->getZ();
		
		if(!($event->getBlock() instanceof Wool)){
		if(!$this->InBlue($player)){
			for($y = 0; $y <= count($this->bpos) - 1; $y++){
				if(($this->bpos[$y][0] <= $x && $this->bpos[$y][1] >= $x) && ($this->bpos[$y][2] <= $z && $this->bpos[$y][3] >= $z)){
					$player->sendMessage(color::BLUE."[AntWar] This land is claimed by the BlueAnts.");
					$event->setCancelled(true);
					$y = count($this->bpos) - 1;
				}
			}
		}
		
	if(!$this->InRed($player)){
		for($y = 0; $y <= count($this->rpos) - 1; $y++){
			if(($this->rpos[$y][0] <= $x && $this->rpos[$y][1] >= $x) && ($this->rpos[$y][2] <= $z && $this->rpos[$y][3] >= $z)){
				$player->sendMessage(color::RED."[AntWar] This land is claimed by the RedAnts.");
				$event->setCancelled(true);
				$y = count($this->rpos) - 1;
			}
		}
	}
	
	if(!$this->InYellow($player)){
		for($y = 0; $y <= count($this->ypos) - 1; $y++){
			if(($this->ypos[$y][0] <= $x && $this->ypos[$y][1] >= $x) && ($this->ypos[$y][2] <= $z && $this->ypos[$y][3] >= $z)){
				$player->sendMessage(color::YELLOW."[AntWar] This land is claimed by the YellowAnts.");
				$event->setCancelled(true);
				$y = count($this->ypos) - 1;
			}
		}
	}
		}	
	}
	
	public function FlagUnset(BlockBreakEvent $block){
		$this->bpos = json_decode(file_get_contents($this->getDataFolder()."bpos.txt"));
		$this->rpos = json_decode(file_get_contents($this->getDataFolder()."rpos.txt"));
		$this->ypos = json_decode(file_get_contents($this->getDataFolder()."ypos.txt"));
		
		if($block->getBlock() instanceof Wool){
			$wool = $block->getBlock();
			if($wool->getDamage() == Wool::BLUE){
				$x = $wool->getX(); $y = $wool->getY(); $z = $wool->getZ();
				$bflag = array();
				for($col = 0; $col <= count($this->bpos) - 1; $col++){
					if(!($this->bpos[$col][0] + 8 == $x && $this->bpos[$col][2] + 8 == $z && $this->bpos[$col][4] == $y)){
						array_push($bflag, $this->bpos[$col]);
					}
				}
				$this->bpos = $bflag;
				file_put_contents($this->getDataFolder()."bpos.txt", json_encode($this->bpos));
			}
			
			if($wool->getDamage() == Wool::RED){
				$x = $wool->getX(); $y = $wool->getY(); $z = $wool->getZ();
				$rFlag = array();
				for($cols = 0; $cols <= count($this->rpos) - 1; $cols++){
					if(!($this->rpos[$cols][0] + 8 == $x && $this->rpos[$cols][2] + 8 == $z && $this->rpos[$cols][4] == $y)){
						array_push($rFlag, $this->rpos[$cols]);
					}
				}
				$this->rpos = $rFlag;
				file_put_contents($this->getDataFolder()."rpos.txt", json_encode($this->rpos));
			}
			
			if($wool->getDamage() == Wool::YELLOW){
				$x = $wool->getX(); $y = $wool->getY(); $z = $wool->getZ();
				$yflag = array();
				for($colss = 0; $colss <= count($this->ypos) - 1; $colss++){
					if(!($this->ypos[$colss][0] + 8 == $x && $this->ypos[$colss][2] + 8 == $z && $this->ypos[$colss][4] == $y)){
						array_push($yflag, $this->ypos[$colss]);
					}
				}
				$this->ypos = $yflag;
				file_put_contents($this->getDataFolder()."ypos.txt", json_encode($this->ypos));
			}
			
		}
	}
	
	/**
	 * @param PlayerJoinEvent $event
	 * @priority HIGHEST
	 */
	
	public function JoinName(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		$this->NameTags($player); 
		if($this->InBlue($player)){
			$player->teleport(new Vector3(-243, 66, 246));
			$player->sendMessage(color::BLUE."[AntWar] Welcome back BlueAnt! Are you ready to serve?");
		}
		if($this->InRed($player)){
			$player->teleport(new Vector3(403, 61, 111));
			$player->sendMessage(color::RED."[AntWar] Welcome back RedAnt! Are you ready to serve?");
		}
		if($this->InYellow($player)){
			$player->teleport(new Vector3(-749, 64, 2077));
			$player->sendMessage(color::YELLOW."[AntWar] Welcome back YellowAnt! Are you ready to serve?");
		}
		if(!$this->InYellow($player) && !$this->InRed($player) && !$this->InBlue($player)){
			$player->sendMessage(color::GOLD."[AntWar] Your brave to fight alone in the War!");
			$player->teleport(new Vector3(-49, 63, -62));
		}
	}
	
	/**
	 * @param PlayerRespawnEvent $event
	 * @priority HIGEST
	 */
	
	public function OnSpawn(PlayerRespawnEvent $event){
		$player = $event->getPlayer();
		if($this->InBlue($player)){
			$player->teleport(new Vector3(-243, 66, 246));
			$player->sendMessage(color::BLUE."[AntWar] Keep Fighting BlueAnt!");
		}
		if($this->InRed($player)){
			$player->teleport(new Vector3(403, 61, 111));
			$player->sendMessage(color::RED."[AntWar] Keep Fighting RedAnt!");
		}
		if($this->InYellow($player)){
			$player->teleport(new Vector3(749, 64, 2077));
			$player->sendMessage(color::YELLOW."[AntWar] Keep Fighting YellowAnt!");
		}
	}
	
	public function NameTags(Player $player){
		$name = $player->getNametag();
		$bstar = color::BLUE."<*>";
		$rstar = color::RED."<=>";
		$ystar = color::YELLOW."<+>";
		if($player instanceof Player){
		if($this->InBlue($player)){
			$player->setNameTag($bstar.$name.$bstar);
		}
		if($this->InRed($player)){
			$player->setNameTag($rstar.$name.$rstar);
		}
		if($this->InYellow($player)){
			$player->setNameTag($ystar.$name.$ystar);
		}
		}
	}
	
	
	public function WarStat(PlayerDeathEvent $event){
		$this->blue = json_decode(file_get_contents($this->getDataFolder()."blue.txt"));
		$this->red = json_decode(file_get_contents($this->getDataFolder()."red.txt"));
		$this->yellow = json_decode(file_get_contents($this->getDataFolder()."yellow.txt"));
		$this->stats = json_decode(file_get_contents($this->getDataFolder()."stats.txt"));
		if($event->getEntity()->getLastDamageCause() instanceof EntityDamageByEntityEvent){
		$death = $event->getEntity();
		if($event->getEntity()->getLastDamageCause()->getDamager() instanceof Player){
		$killer = $event->getEntity()->getLastDamageCause()->getDamager();
		if(in_array(strtolower($death->getName()), $this->blue)){
			$this->stats[0][0] += 1;
		}
		if(in_array(strtolower($killer->getName()), $this->blue)){
			$this->stats[0][1] += 1;
		}
		
		if(in_array(strtolower($death->getName()), $this->red)){
			$this->stats[1][0] += 1;
		}
		if(in_array(strtolower($killer->getName()), $this->red)){
			$this->stats[1][1] += 1;
		}
		
		if(in_array(strtolower($death->getName()), $this->yellow)){
			$this->stats[2][0] += 1;
		}
		if(in_array(strtolower($killer->getName()), $this->yellow)){
			$this->stats[2][1] += 1;
		}
		file_put_contents($this->getDataFolder()."stats.txt", json_encode($this->stats));
		}
		}
	}
	
	public function Reset(){
		$this->FlagReset($this->getServer()->getLevel(1));
		$this->blue = array();
		$this->red = array();
		$this->yellow = array();
		$this->bpos = array();
		$this->rpos = array();
		$this->ypos = array();
		$this->stats = array(array(0,0), array(0,0), array(0,0));
		file_put_contents($this->getDataFolder()."blue.txt", json_encode($this->blue));
		file_put_contents($this->getDataFolder()."red.txt", json_encode($this->red));
		file_put_contents($this->getDataFolder()."yellow.txt", json_encode($this->yellow));
		file_put_contents($this->getDataFolder()."bpos.txt", json_encode($this->bpos));
		file_put_contents($this->getDataFolder()."rpos.txt", json_encode($this->rpos));
		file_put_contents($this->getDataFolder()."ypos.txt", json_encode($this->ypos));
		file_put_contents($this->getDataFolder()."stats.txt", json_encode($this->stats));
	}
	
	public function FlagReset(Level $lvl){
		$this->blue = json_decode(file_get_contents($this->getDataFolder()."blue.txt"));
		$this->red = json_decode(file_get_contents($this->getDataFolder()."red.txt"));
		$this->yellow = json_decode(file_get_contents($this->getDataFolder()."yellow.txt"));
		for($x = 0; $x<= count($this->bpos)- 1; $x++){
			$lvl->setBlock(new Vector3($this->bpos[$x][0] + 8, $this->bpos[$x][4], $this->bpos[$x][2] + 8), Block::get(0));
		}
		
		for($x = 0; $x<= count($this->rpos)- 1; $x++){
			$lvl->setBlock(new Vector3($this->rpos[$x][0] + 8, $this->rpos[$x][4], $this->rpos[$x][2] + 8), Block::get(0));
		}
		
		for($x = 0; $x<= count($this->ypos)- 1; $x++){
			$lvl->setBlock(new Vector3($this->ypos[$x][0] + 8, $this->ypos[$x][4], $this->ypos[$x][2] + 8), Block::get(0));
		}
	}
	
	public function RemovePlayer(Player $player){
		$this->blue = json_decode(file_get_contents($this->getDataFolder()."blue.txt"));
		$this->red = json_decode(file_get_contents($this->getDataFolder()."red.txt"));
		$this->yellow = json_decode(file_get_contents($this->getDataFolder()."yellow.txt"));
		if($player instanceof Player){
			if($this->InBlue($player)){
				$bplayers = array();
				for($b = 0; $b <= count($this->blue) - 1; $b++){
					if(strtolower($player->getName()) != $this->blue[$b]){
						array_push($bplayers, $this->blue[$b]);
					}
				}
				$this->blue = $bplayers;
				file_put_contents($this->getDataFolder()."blue.txt", json_encode($this->blue));
				$player->kick("You have been removed from War");
			}
			
			if($this->InRed($player)){
				$rplayers = array();
				for($r = 0; $r <= count($this->red)- 1; $r++){
					if(strtolower($player->getName()) != $this->red[$r] ){
						array_push($rplayers, $this->red[$r]);
					}
				}
				$this->red = $rplayers;
				file_put_contents($this->getDataFolder()."red.txt", json_encode($this->red));
				$player->kick("You have been removed from War");
			}
			
			if($this->InYellow($player)){
				$yplayers = array();
				for($y = 0; $y <= count($this->yellow) - 1; $y++){
					if(strtolower($player->getName()) != $this->yellow[$y]){
						array_push($yplayers, $this->yellow[$y]);
					}
				}
				$this->yellow = $yplayers;
				file_put_contents($this->getDataFolder()."yellow.txt", json_encode($this->yellow));
				$player->kick("You have been removed from War");
			}
		}
	}
}
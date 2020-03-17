<?php
namespace Ant;


use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\entity\Effect;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\Player;
use Ant\TimePreGame;
use Ant\TimeGame;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\command\ConsoleCommandSender;

class InfectionMain extends PluginBase implements Listener{
	
	public $players = array();
	public $infected = array();
	
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->saveDefaultConfig();
		$this->getLogger()->info(TextFormat::GREEN."AntInfection is ON!");
		if(!file_exists($this->getDataFolder())){
			mkdir($this->getDataFolder());
		}
		if(!file_exists($this->getDataFolder()."players.txt")){
			file_put_contents($this->getDataFolder()."players.txt", json_encode($this->players));
		}
		if(!file_exists($this->getDataFolder()."mode.txt")){
			file_put_contents($this->getDataFolder()."mode.txt", "nogame");
		}
		if(!file_exists($this->getDataFolder()."infected.txt")){
			file_put_contents($this->getDataFolder()."infected", json_encode($this->infected));
		}
	}
	
	public function SignMake(SignChangeEvent $event){
		$c = $this->getConfig()->getAll();
		$this->players = json_decode(file_get_contents($this->getDataFolder()."players.txt"));
		
		if(strtolower(trim($event->getLine(0))) == "[antzombie]"){
			
			if($event->getPlayer()->hasPermission("infection")){
				$event->setLine(1, count($this->players)."/".$c["max"]);	
				$event->setLine(2, TextFormat::GREEN."[Join]");
				$event->setLine(0, TextFormat::GREEN."[AntZombie]");
			}
			else{
				$event->setCancelled(true);
			}
			
		}
	}
	
	public function SignTouch(PlayerInteractEvent $event){
		$c = $this->getConfig()->getAll();
		$mode = file_get_contents($this->getDataFolder()."mode.txt");
		$this->players = json_decode(file_get_contents($this->getDataFolder()."players.txt"));
		$b = $event->getBlock();
		if($b->getId() == Item::WALL_SIGN || $b->getId() == Item::SIGN_POST){
			$sign = $event->getPlayer()->getLevel()->getTile(new Vector3($b->getX(), $b->getY(), $b->getZ()));
			if(TextFormat::clean($sign->getText()[0]) == "[AntZombie]" || TextFormat::clean($sign->getText()[0]) == TextFormat::GREEN."[AntZombie]" ){
				if ($mode == "game"){
					$event->getPlayer()->sendMessage(TextFormat::RED."Game has started! Please Wait To Play Later Game!");
				}
				else if(count($this->players) < $c["max"]){
				    
					$event->getPlayer()->teleport(new Vector3($c["spawnx"],$c["spawny"],$c["spawnz"]));
					$event->getPlayer()->sendMessage(TextFormat::GREEN."Welcome to Ant Infection!");
					$event->getPlayer()->setGamemode(0);
					$name = strtolower($event->getPlayer()->getName());
					array_push($this->players, $name);
					$sign->setText(TextFormat::GREEN."[AntZombie]", TextFormat::BLACK.count($this->players)."/".$c["max"],  TextFormat::GREEN."[Join]","");
					file_put_contents($this->getDataFolder()."players.txt", json_encode($this->players));
					if(count($this->players) >= 2 && $mode == "nogame"){
						$this->PreGame();
					}
				}
				else if(count($this->players) == c["max"]){
					$event->getPlayer()->sendMessage(TextFormat::RED."The Arena is full!");
				}
			}
		}
	}
	
	public function PlayerLeave(PlayerQuitEvent $event){
		$this->players = json_decode(file_get_contents($this->getDataFolder()."players.txt"));
		$mode = file_get_contents($this->getDataFolder()."mode.txt");
		
			if(in_array(strtolower($event->getPlayer()->getName()), $this->players) ){
			$players = array();
			foreach($this->players as $player){
			
			if($player != strtolower($event->getPlayer()->getName()){
			array_push($players, $player);
				} 
			}
			file_put_contents($this->getDataFolder()."players.txt", json_encode($this->players));
			if( $mode == "pregame" && count($players) < 2){
			file_put_contents($this->getDataFolder()."mode.txt", "nogame");
			}
		}
		
	}
	
	public function PreGame(){
		$mode = file_get_contents($this->getDataFolder()."mode.txt");
		if($mode == "nogame"){
		file_put_contents($this->getDataFolder()."mode.txt", "pregame");
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new TimePreGame($this), 20);
		}
	}
	
	public function Session(){
		file_put_contents($this->getDataFolder()."mode.txt", "game");
		$c = $this->getConfig()->getAll();
		$this->players = json_decode(file_get_contents($this->getDataFolder()."players.txt"));
		foreach ($this->players as $player){
			$p = $this->getServer()->getPlayer($player);
			$p->teleport(new Vector3($c["gamespawnx"], $c["gamespawny"], $c["gamespawnz"]));
			$p->getInventory()->clearAll();
			$p->removeAllEffects();
		}
		$rand = rand(0, count($this->players) - 1);
		$peep = $this->getServer()->getPlayer($this->players[$rand]);
		$peep->addEffect(Effect::getEffect(9)->setAmplifier(1)->setDuration(9999999999999));
		array_push($this->infected, strtolower($peep->getName()));
		file_put_contents($this->getDataFolder()."infected", json_encode($this->infected));
		
		foreach ($this->players as $play){
			$pp = $this->getServer()->getPlayer($play);
			$pp->sendMessage(TextFormat::GREEN.$peep->getName()." is Infected");
		}
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new TimeGame($this), 1200);

	}

	public function PlayerInfect(EntityDamageEvent $event){
		$this->players = json_decode(file_get_contents($this->getDataFolder()."players.txt"));
		$mode = file_get_contents($this->getDataFolder()."mode.txt");
		if($mode == "game" || $mode == "pregame"){
		if($event->getCause() == EntityDamageByEntityEvent::CAUSE_ENTITY_ATTACK || $event instanceof EntityDamageByEntityEvent){
			$player = $event->getEntity();
			$sick = $event->getDamager();
			if(in_array(strtolower($player->getName()), $this->players) && in_array(strtolower($sick->getName()), $this->players)){
				
					if(in_array(strtolower($sick->getName()), $this->infected) && !in_array(strtolower($player->getName()), $this->infected)){
                        $player->addEffect(Effect::getEffect(9)->setAmplifier(1)->setDuration(9999999999999));
						array_push($this->infected, strtolower($player->getName()));
						file_put_contents($this->getDataFolder()."infected", json_encode($this->infected));
						$event->setCancelled();
					}
				
					
		}
		}
		}}
	
	public function SessionEnd(){
		$c = $this->getConfig()->getAll();
		$this->players = json_decode(file_get_contents($this->getDataFolder()."players.txt"));
	
		foreach ($this->players as $pepe){
			if(in_array($pepe, $this->infected)){
				$this->getServer()->getPlayer($pepe)->kill();
			}
			
		}

	
		foreach ($this->players as $player){
			if(!in_array($player, $this->infected)){
			$p = $this->getServer()->getPlayer($player);
			$p->teleport(new Vector3($c["realspawnx"],$c["realspawny"],$c["realspawnz"]));
			$p->sendMessage(TextFormat::GREEN."You won the game and won 20 emeralds");
			$p->getInventory()->addItem(Item::get(388, 0, 20));
			}
		}
		file_put_contents($this->getDataFolder()."players.txt", json_encode(array()));
		$this->infected = array();
		file_put_contents($this->getDataFolder()."infected", json_encode($this->infected));
		file_put_contents($this->getDataFolder()."mode.txt", "nogame");
	}
	public function BanCommand(PlayerCommandPreprocessEvent $event){
		$this->players = json_decode(file_get_contents($this->getDataFolder()."players.txt"));
		if(in_array(strtolower($event->getPlayer()->getName()), $this->players)){
		$cmds = array("spawn", "home", "tp", "gamemode", "gmc", "sethome", "tpahere", "tphere", "tpahere",  "suicide", "kill", "fly", "vanish", "effect", "tpa", "kit",  "sudo", "effect", "tpaccept");
		foreach($cmds as $cmd){
		if($event->getMessage() == "/".$cmd){
			$event->getPlayer()->sendMessage(TextFormat::RED."You can't use that command while playing the game!");
			$event->setCancelled(true);
		}
		}
		}
	}
 	public function onDisable(){
		file_put_contents($this->getDataFolder()."mode.txt", "nogame");
		$this->players = array();
		file_put_contents($this->getDataFolder()."players.txt", json_encode($this->players));
	}
}

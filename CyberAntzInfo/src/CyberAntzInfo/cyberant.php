<?php
namespace CyberAntzInfo;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as color;
use pocketmine\Player;

Class cyberant extends PluginBase{
	
	Public function onEnable(){
		$this->getLogger()->info(color::GOLD."Cyber".color::GREEN."Ant Enabled");
	}
	
	Public function onCommand(CommandSender $sender,Command $command, $label, array $args){
				if($sender instanceof Player){
			if(strtolower($command->getName()) == 'cyberant'){
				switch(strtolower($args[0])){
					case "vote";
						$sender->sendMessage(color::GOLD."[Cyber".color::GREEN."Antz] \nGo Vote at \nhttp://minecraftpocket-servers.com/server/40089/");
						break;
					case "rank";
						$sender->sendMessage(color::GOLD."[Cyber".color::GREEN."Antz] \nYou can unlock ranks by voting and donating (Donate at CyberAntz.net)");
						break;
					case "info";
						$sender->sendMessage(color::GOLD."[Cyber".color::GREEN."Antz] \nOwner: Javascript\nKik: RevivalTeam\nEmail: CyberAntzMCPE@gmail.com");
						break;
						default;
						$sender->sendMessage(color::GOLD."[Cyber".color::GREEN."Ant] /cyberant (vote|rank|info)");
						break;
				}
			}
		}
	}
}

<?php
namespace Shop;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat as color;

use pocketmine\event\block\SignChangeEvent;
use pocketmine\item\Item;
use pocketmine\block\Block;
use pocketmine\item\ItemBlock;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\tile\Sign;
use pocketmine\math\Vector3;


class ShopMain extends PluginBase implements Listener{
	
	public function onEnable(){
		$this->getLogger()->info(color::GREEN."[GemShop] Enabled");
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		
	}
	
	public function SetSign(SignChangeEvent $event){
		$player = $event->getPlayer();
		
		if(strtolower(trim($event->getLine(0))) == "[gembuy]"){//buy
			if($player->hasPermission("shop.create") || $player->hasPermission("shop")){
				$line1 = explode("*", trim($event->getLine(1)));
				if(!isset($line1[0]) || !isset($line1[1])){
					$player->sendMessage(color::RED."Please type first amount then item id on Line 2");
					$event->setCancelled(true);
					return;
				}
				if(Item::fromString($line1[1]) instanceof Item){
					$item = Item::fromString($line1[1]);
					$event->setLine(2, $item->getName());
				}
				else if(ItemBlock::fromString($line1[1]) instanceof ItemBlock || Block::get(intval($line1[1])) instanceof Block){
					$block = ItemBlock::fromString($line1[1]);
					$event->setLine(2, $block->getName());
				}
				else{
					$player->sendMessage(color::RED."Please type a valid id");
					$event->setCancelled(true);
					return;
				}
				if($event->getLine(3) !== null){
					$line3 = explode(" ", trim($event->getLine(3)));
					if(is_int(intval($line3[0])) && intval($line3[0]) > 0){
							$event->setLine(3, $line3[0]." Gems");
					}
					else{
						$player->sendMessage(color::RED."Please type the amount of gems for Line 4");
						$event->setCancelled(true);
						return;
					}
				}
				else{
					$player->sendMessage(color::RED."Please type the amount of gems for Line 4");
					$event->setCancelled(true);
				}
				$event->setLine(0, "[GemBuy]");
			}
			else{
				$event->setCancelled(true);
				$player->sendMessage(color::RED."You Don't Have Permission!");
			}
		}
		
		if(strtolower(trim($event->getLine(0))) == "[gemsell]"){//sell
			if($player->hasPermission("shop.create") || $player->hasPermission("shop")){
				$line1 = explode("*", trim($event->getLine(1)));
				if(!isset($line1[0]) || !isset($line1[1])){
					$player->sendMessage(color::RED."Please type first amount then item id on Line 2");
					$event->setCancelled(true);
					return;
				}
				if(Item::fromString($line1[1]) instanceof Item){
					$item = Item::fromString($line1[1]);
					$event->setLine(2, $item->getName());
				}
				else if(ItemBlock::fromString($line1[1]) instanceof ItemBlock || Block::get(intval($line1[1])) instanceof Block){
					$block = ItemBlock::fromString($line1[1]);
					$event->setLine(2, $block->getName());
				}
				else{
					$player->sendMessage(color::RED."Please type a valid id");
					$event->setCancelled(true);
					return;
				}
				if($event->getLine(3) !== null){
					$line3 = explode(" ", trim($event->getLine(3)));
					if(is_int(intval($line3[0])) && intval($line3[0]) > 0){
							$event->setLine(3, $line3[0]." Gems");
					}
					else{
						$player->sendMessage(color::RED."Please type the amount of gems for Line 4");
						$event->setCancelled(true);
						return;
					}
				}
				else{
					$player->sendMessage(color::RED."Please type the amount of gems for Line 4");
					$event->setCancelled(true);
				}
				$event->setLine(0, "[GemSell]");
			}
			else{
				$event->setCancelled(true);
				$player->sendMessage(color::RED."You Don't Have Permission!");
			}
		}
		
	}
	
	public function PlayerTouch(PlayerInteractEvent $event){
		$block = $event->getBlock();
		if($block->getId() == Item::SIGN_POST || $block->getId() == Item::WALL_SIGN){
			$player = $event->getPlayer();
			$sign = $block->getLevel()->getTile(new Vector3($block->getFloorX(), $block->getFloorY(), $block->getFloorZ()));
			if(!($player->isCreative())){
			if(color::clean($sign->getText()[0]) == "[GemSell]"){
				$line1 = explode("*", color::clean($sign->getText()[1]));
				if($this->HasAmount($player, Item::fromString($line1[1]))){
					$line3 = color::clean($sign->getText()[3]);
					$gems = explode(" ", $line3);
					$player->getInventory()->addItem(Item::get(388, 0, $gems[0]));
				for($x = 0; $x <= intval($line1[0]) - 1;$x++){
					$player->getInventory()->removeItem(Item::fromString($line1[1]));
					}
					
					$player->sendMessage(color::GREEN."[GemShop] You received ".$gems[0]." Gems!");
			}
			else{
				$player->sendMessage(color::RED."[GemShop] You dont have enough to sell!");
				$event->setCancelled(true);
			}
			}
			
			if(color::clean($sign->getText()[0]) == "[GemBuy]"){
				$line1 = explode("*", color::clean($sign->getText()[1]));
				$line3 = color::clean($sign->getText()[3]);
				$gems = explode(" ", $line3);
				$item = new Item(388, 0, $gems[0]);
				if($this->HasAmount($player, Item::fromString($line1[1]))){
					$player->getInventory()->removeItem($item);
					for($x = 0; $x <= intval($line1[0]) - 1;$x++){
					$player->getInventory()->addItem(Item::fromString($line1[1]));
					}
					$player->sendMessage(color::GREEN."[GemShop] You bought ".$line1[0]." ".$sign->getText()[2]."!");
				}
				else{
					$player->sendMessage(color::RED."[GemShop] You dont have enough gems!");	
					$event->setCancelled(true);
				}
			}
			}
			else{
				$player->sendMessage(color::RED."You can't use shop in creative");
				$event->setCancelled(true);
			}
		}
	}
	
	public function HasAmount(Player $player, Item $item){
		$count = 0;
		for($x = 0; $x <= $player->getInventory()->getSize(); $x++){
			$inv = $player->getInventory()->getItem($x);
			if($inv->getId() == $item->getId() && $inv->getDamage() == $item->getDamage()){
				$count += $inv->getCount();
			}
		}
		if($count >= $item->getCount()){
			return true;
		}
		return false;
	}
}
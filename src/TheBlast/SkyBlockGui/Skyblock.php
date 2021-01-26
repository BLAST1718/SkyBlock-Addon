<?php

namespace TheBlast\Menu;

use jojoe77777\formapi\CustomForm;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\InvMenuHandler;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use room17\SkyBlock\SkyBlock;
use room17\SkyBlock\island\IslandFactory;
use room17\SkyBlock\session\Session;
use room17\SkyBlock\session\SessionLocator;
use room17\SkyBlock\utils\Invitation;
use room17\SkyBlock\utils\message\MessageContainer;

class Main extends PluginBase{

	public function onEnable(){
		$this->getLogger()->info("SkyBlock Menu enabled made by SkyCraft");
		$api = SkyBlock::getInstance();
		if(!InvMenuHandler::isRegistered()){
			InvMenuHandler::register($this);
		}
		$command = new PluginCommand("sb", $this);
		$command->setDescription("Skyblock Menu");
		$this->getServer()->getCommandMap()->register("sb", $command);
	}

	public function onDisable(){
		$this->getLogger()->info("SkyBlock Menu disabled made by SkyCraft");
	}

	public function onCommand(CommandSender $player, Command $cmd, string $label, array $args) : bool{
        switch($cmd->getName()){
            case "sb":
                if(!$player instanceof Player){
                    $player->sendMessage("SkyBlock Menu");
                    return true;
                }
                if (!$session->hasIsland()) {
                        $this->islandCreation($player, $session);
                    } else {
                        $this->islandManagement($player, $session);
                }
                    break;
        }
        return true;
    }

	public function islandManagement(Player $player){
		$menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
		$menu->readOnly();
		$menu->setListener(\Closure::fromCallable([$this, "sbmenu"]));
		$menu->setName("Menu");
		$inv = $menu->getInventory();
		$skull = Item::get(397, 3)->setCustomName("§r§aProfile");
		$paper = Item::get(339)->setCustomName("§r§bInfo");
		$painting = Item::get(321)->setCustomName("§r§eRules");
		$book = Item::get(340)->setCustomName("§r§aRecipe Book");
		$emerald = Item::get(388)->setCustomName("§r§aIsland Menu");
		$written_book = Item::get(387)->setCustomName("§r§5Missions");
		$clock = Item::get(347)->setCustomName("§r§eEvent");
		$echest = Item::get(130)->setCustomName("§r§5EnderChest Inventory");
		$craft = Item::get(58)->setCustomName("§r§6Crafting Menu");
		$feather = Item::get(288)->setCustomName("§r§6Island Go");
		$compass = Item::get(345)->setCustomName("§r§aWarp");
		$diamond = Item::get(264)->setCustomName("§r§bSelect Servers");
		$green_wool = Item::get(35, 5)->setCustomName("§r§eShop");
		$leaves = Item::get(-161)->setCustomName("§r§cClose");
		$torch = Item::get(76)->setCustomName("§r§7Settings");
		$green_glass = Item::get(160, 5)->setCustomName("")->setLore([""]);
		$inv->setItem(0, $green_glass);
		$inv->setItem(1, $green_glass);
		$inv->setItem(2, $green_glass);
		$inv->setItem(3, $green_glass);
		$inv->setItem(4, $green_glass);
		$inv->setItem(5, $green_glass);
		$inv->setItem(6, $green_glass);
		$inv->setItem(7, $green_glass);
		$inv->setItem(8, $green_glass);
		$inv->setItem(9, $green_glass);
		$inv->setItem(10, $green_glass);
		$inv->setItem(11, $green_glass);
		$inv->setItem(12, $green_glass);
		$inv->setItem(13, $skull);
		$inv->setItem(14, $green_glass);
		$inv->setItem(15, $green_glass);
		$inv->setItem(16, $green_glass);
		$inv->setItem(17, $green_glass);
		$inv->setItem(18, $green_glass);
		$inv->setItem(19, $green_wool);
		$inv->setItem(20, $painting);
		$inv->setItem(21, $book);
		$inv->setItem(22, $emerald);
		$inv->setItem(23, $written_book);
		$inv->setItem(24, $clock);
		$inv->setItem(25, $echest);
		$inv->setItem(26, $green_glass);
		$inv->setItem(27, $green_glass);
		$inv->setItem(28, $green_glass);
		$inv->setItem(29, $green_glass);
		$inv->setItem(30, $green_glass);
		$inv->setItem(31, $craft);
		$inv->setItem(32, $green_glass);
		$inv->setItem(33, $green_glass);
		$inv->setItem(34, $green_glass);
		$inv->setItem(35, $green_glass);
		$inv->setItem(36, $feather);
		$inv->setItem(37, $green_glass);
		$inv->setItem(38, $green_glass);
		$inv->setItem(39, $green_glass);
		$inv->setItem(40, $green_glass);
		$inv->setItem(41, $green_glass);
		$inv->setItem(42, $green_glass);
		$inv->setItem(43, $green_glass);
		$inv->setItem(44, $green_glass);
		$inv->setItem(45, $compass);
		$inv->setItem(46, $green_glass);
		$inv->setItem(47, $green_glass);
		$inv->setItem(48, $diamond);
		$inv->setItem(49, $leaves);
		$inv->setItem(50, $torch);
		$inv->setItem(51, $green_glass);
		$inv->setItem(52, $green_glass);
		$inv->setItem(53, $green_glass);
		$menu->send($player);
	}

	public function sbmenu(InvMenuTransaction $action) : InvMenuTransactionResult{
		$item = $action->getOut();
		$player = $action->getPlayer();
		$itemClicked = $item;
		if($item->getCustomName() === "§r§aProfile"){
			$inv = $action->getAction()->getInventory();
			$inv->onClose($player);
			return $action->discard()->then(function(Player $player) : void{
			         $this->Profile($player);
			});
		}

		if($item->getId() == 339){
			$action->getAction()->getInventory()->onClose($player);
			$this->info($player);
			return $action->discard();
		}
		if($item->getId() == 321){
			$action->getAction()->getInventory()->onClose($player);
			$this->rules($player);
			return $action->discard();
		}
		if($item->getId() == 340){
			$action->getAction()->getInventory()->onClose($player);
			$this->recipes($player);
			return $action->discard();
		}
		if($item->getId() == 388){
			$action->getAction()->getInventory()->onClose($player);
			\pocketmine\Server::getInstance()->dispatchCommand($player, "sb");
			return $action->discard();
		}
		if($item->getId() == 387){
			$action->getAction()->getInventory()->onClose($player);
			\pocketmine\Server::getInstance()->dispatchCommand($player, "mission");
			return $action->discard();
		}
		if($item->getId() == 347){
			$action->getAction()->getInventory()->onClose($player);
			$this->events($player);
			return $action->discard();
		}
		if($item->getCustomName() === "§r§5EnderChest Inventory"){
			$action->getAction()->getInventory()->onClose($player);
			\pocketmine\Server::getInstance()->dispatchCommand($player, "ec");
			return $action->discard();
		}
		if($item->getCustomName() === "§r§6Crafting Menu"){
			$action->getAction()->getInventory()->onClose($player);
			\pocketmine\Server::getInstance()->dispatchCommand($player, "craft");
			return $action->discard();
		}
		if($item->getId() == 288){
			$action->getAction()->getInventory()->onClose($player);
			\pocketmine\Server::getInstance()->dispatchCommand($player, "is go");
			return $action->discard();
		}
		if($item->getId() == 345){
			$action->getAction()->getInventory()->onClose($player);
			$this->wp1($player);
			return $action->discard();
		}
		if($item->getId() == 264){
			$action->getAction()->getInventory()->onClose($player);
			$this->servers($player);
			return $action->discard();
		}
		if($item->getId() == -161){
			$action->getAction()->getInventory()->onClose($player);
			\pocketmine\Server::getInstance()->dispatchCommand($player, "");
			return $action->discard();
		}
		if($itemClicked->getId() == 160 && $itemClicked->getDamage() === 5){
			$action->getAction()->getInventory()->onClose($player);
			\pocketmine\Server::getInstance()->dispatchCommand($player, "");
			return $action->discard();
		}
		if($item->getId() == 76){
			$action->getAction()->getInventory()->onClose($player);
			$this->settings($player);
			return $action->discard();
		}
		if($itemClicked->getId() == 35 && $itemClicked->getDamage() === 5){
			$action->getAction()->getInventory()->onClose($player);
			\pocketmine\Server::getInstance()->dispatchCommand($player, "shop");
			return $action->discard();
		}
		return $action->discard();
	}

	public function islandCreation(Player $player){
		$menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
		$menu->readOnly();
		$menu->setListener(\Closure::fromCallable([$this, "sbmenu"]));
		$menu->setName("Menu");
		$inv = $menu->getInventory();
		$skull = Item::get(397, 3)->setCustomName("§r§aProfile");
		$paper = Item::get(339)->setCustomName("§r§bInfo");
		$painting = Item::get(321)->setCustomName("§r§eRules");
		$book = Item::get(340)->setCustomName("§r§aRecipe Book");
		$emerald = Item::get(388)->setCustomName("§r§aIsland Menu");
		$written_book = Item::get(387)->setCustomName("§r§5Missions");
		$clock = Item::get(347)->setCustomName("§r§eEvent");
		$echest = Item::get(130)->setCustomName("§r§5EnderChest Inventory");
		$craft = Item::get(58)->setCustomName("§r§6Crafting Menu");
		$feather = Item::get(288)->setCustomName("§r§6Island Go");
		$compass = Item::get(345)->setCustomName("§r§aWarp");
		$diamond = Item::get(264)->setCustomName("§r§bSelect Servers");
		$green_wool = Item::get(35, 5)->setCustomName("§r§eShop");
		$leaves = Item::get(-161)->setCustomName("§r§cClose");
		$torch = Item::get(76)->setCustomName("§r§7Settings");
		$green_glass = Item::get(160, 5)->setCustomName("")->setLore([""]);
		$inv->setItem(0, $green_glass);
		$inv->setItem(1, $green_glass);
		$inv->setItem(2, $green_glass);
		$inv->setItem(3, $green_glass);
		$inv->setItem(4, $green_glass);
		$inv->setItem(5, $green_glass);
		$inv->setItem(6, $green_glass);
		$inv->setItem(7, $green_glass);
		$inv->setItem(8, $green_glass);
		$inv->setItem(9, $green_glass);
		$inv->setItem(10, $green_glass);
		$inv->setItem(11, $green_glass);
		$inv->setItem(12, $green_glass);
		$inv->setItem(13, $skull);
		$inv->setItem(14, $green_glass);
		$inv->setItem(15, $green_glass);
		$inv->setItem(16, $green_glass);
		$inv->setItem(17, $green_glass);
		$inv->setItem(18, $green_glass);
		$inv->setItem(19, $green_wool);
		$inv->setItem(20, $painting);
		$inv->setItem(21, $book);
		$inv->setItem(22, $emerald);
		$inv->setItem(23, $written_book);
		$inv->setItem(24, $clock);
		$inv->setItem(25, $echest);
		$inv->setItem(26, $green_glass);
		$inv->setItem(27, $green_glass);
		$inv->setItem(28, $green_glass);
		$inv->setItem(29, $green_glass);
		$inv->setItem(30, $green_glass);
		$inv->setItem(31, $craft);
		$inv->setItem(32, $green_glass);
		$inv->setItem(33, $green_glass);
		$inv->setItem(34, $green_glass);
		$inv->setItem(35, $green_glass);
		$inv->setItem(36, $feather);
		$inv->setItem(37, $green_glass);
		$inv->setItem(38, $green_glass);
		$inv->setItem(39, $green_glass);
		$inv->setItem(40, $green_glass);
		$inv->setItem(41, $green_glass);
		$inv->setItem(42, $green_glass);
		$inv->setItem(43, $green_glass);
		$inv->setItem(44, $green_glass);
		$inv->setItem(45, $compass);
		$inv->setItem(46, $green_glass);
		$inv->setItem(47, $green_glass);
		$inv->setItem(48, $diamond);
		$inv->setItem(49, $leaves);
		$inv->setItem(50, $torch);
		$inv->setItem(51, $green_glass);
		$inv->setItem(52, $green_glass);
		$inv->setItem(53, $green_glass);
		$menu->send($player);
	}

	public function sbmenu(InvMenuTransaction $action) : InvMenuTransactionResult{
		$item = $action->getOut();
		$player = $action->getPlayer();
		$itemClicked = $item;
		if($item->getCustomName() === "§r§aProfile"){
			$inv = $action->getAction()->getInventory();
			$inv->onClose($player);
			return $action->discard()->then(function(Player $player) : void{
			         $this->Profile($player);
			});
		}

		if($item->getId() == 339){
			$action->getAction()->getInventory()->onClose($player);
			$this->info($player);
			return $action->discard();
		}
		if($item->getId() == 321){
			$action->getAction()->getInventory()->onClose($player);
			$this->rules($player);
			return $action->discard();
		}
		if($item->getId() == 340){
			$action->getAction()->getInventory()->onClose($player);
			$this->recipes($player);
			return $action->discard();
		}
		if($item->getId() == 388){
			$action->getAction()->getInventory()->onClose($player);
			\pocketmine\Server::getInstance()->dispatchCommand($player, "sb");
			return $action->discard();
		}
		if($item->getId() == 387){
			$action->getAction()->getInventory()->onClose($player);
			\pocketmine\Server::getInstance()->dispatchCommand($player, "mission");
			return $action->discard();
		}
		if($item->getId() == 347){
			$action->getAction()->getInventory()->onClose($player);
			$this->events($player);
			return $action->discard();
		}
		if($item->getCustomName() === "§r§5EnderChest Inventory"){
			$action->getAction()->getInventory()->onClose($player);
			\pocketmine\Server::getInstance()->dispatchCommand($player, "ec");
			return $action->discard();
		}
		if($item->getCustomName() === "§r§6Crafting Menu"){
			$action->getAction()->getInventory()->onClose($player);
			\pocketmine\Server::getInstance()->dispatchCommand($player, "craft");
			return $action->discard();
		}
		if($item->getId() == 288){
			$action->getAction()->getInventory()->onClose($player);
			\pocketmine\Server::getInstance()->dispatchCommand($player, "is go");
			return $action->discard();
		}
		if($item->getId() == 345){
			$action->getAction()->getInventory()->onClose($player);
			$this->wp1($player);
			return $action->discard();
		}
		if($item->getId() == 264){
			$action->getAction()->getInventory()->onClose($player);
			$this->servers($player);
			return $action->discard();
		}
		if($item->getId() == -161){
			$action->getAction()->getInventory()->onClose($player);
			\pocketmine\Server::getInstance()->dispatchCommand($player, "");
			return $action->discard();
		}
		if($itemClicked->getId() == 160 && $itemClicked->getDamage() === 5){
			$action->getAction()->getInventory()->onClose($player);
			\pocketmine\Server::getInstance()->dispatchCommand($player, "");
			return $action->discard();
		}
		if($item->getId() == 76){
			$action->getAction()->getInventory()->onClose($player);
			$this->settings($player);
			return $action->discard();
		}
		if($itemClicked->getId() == 35 && $itemClicked->getDamage() === 5){
			$action->getAction()->getInventory()->onClose($player);
			\pocketmine\Server::getInstance()->dispatchCommand($player, "shop");
			return $action->discard();
		}
		return $action->discard();
	}

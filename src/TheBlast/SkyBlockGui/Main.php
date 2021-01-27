<?php

namespace TheBlast\SkyBlockGui;

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
		@mkdir($this->getDataFolder());
        $this->saveResource("config.yml");
        $this->saveDefaultConfig();
		$this->getLogger()->info("enabled");
		$api = SkyBlock::getInstance();
		if(!InvMenuHandler::isRegistered()){
			InvMenuHandler::register($this);
		}
		$command = new PluginCommand("sb1", $this);
		$command->setDescription("Skyblock Menu");
		$this->getServer()->getCommandMap()->register("sb1", $command);
	}

	public function onDisable(){
		$this->getLogger()->info("disabled");
	}

	public function onCommand(CommandSender $player, Command $cmd, string $label, array $args) : bool{
        switch($cmd->getName()){
            case "sb1":
                if(!$player instanceof Player){
                    $player->sendMessage("SkyBlockGui");
                    return true;
                }
		$session = SessionLocator::getSession($player);
                if (!$session->hasIsland()) {
                        $this->islandCreation($player, $session);
                    } else {
                        $this->islandManagement($player, $session);
                }
                    break;
        }
        return true;
    }

	public function islandCreation(Player $player){
		$menu = InvMenu::create(InvMenu::TYPE_CHEST);
		$menu->readOnly();
		$menu->setListener(\Closure::fromCallable([$this, "iscreate"]));
		$menu->setName($this->getConfig()->get("Island-Creation-Menu-Name"));
		$inv = $menu->getInventory();
		$item = Item::get($this->getConfig()->get("Item-id-1"))->setCustomName($this->getConfig()->get("Create-Island-Item-Name"));
		$item2 = Item::get($this->getConfig()->get("Item-id-2"))->setCustomName($this->getConfig()->get("Invite-Manage-Item-Name"));
		$inv->setItem(10, $item);
		$inv->setItem(16, $item2);
		$menu->send($player);
	}

	public function iscreate(InvMenuTransaction $action) : InvMenuTransactionResult{
		$item = $action->getOut();
		$player = $action->getPlayer();
		$itemClicked = $item;
		if($item->getId() == ($this->getConfig()->get("Item-id-1"))){
			$inv = $action->getAction()->getInventory();
			$inv->onClose($player);
			return $action->discard()->then(function(Player $player) : void{
			         $this->IslandCreation2($player);
			});
		}
		if($item->getId() == ($this->getConfig()->get("Item-id-2"))){
			$inv = $action->getAction()->getInventory();
			$inv->onClose($player);
			return $action->discard()->then(function(Player $player) : void{
			         $this->IslandInvite($player);
			});
		}

		return $action->discard();
	}

	public function islandCreation2(Player $player){
		$menu = InvMenu::create(InvMenu::TYPE_CHEST);
		$menu->readOnly();
		$menu->setListener(\Closure::fromCallable([$this, "iscreate2"]));
		$menu->setName($this->getConfig()->get("Choose-Island-Menu-Name"));
		$inv = $menu->getInventory();
		$item3 = Item::get($this->getConfig()->get("Item-id-3"))->setCustomName($this->getConfig()->get("Basic-Island-Item-Name"));
		$item4 = Item::get($this->getConfig()->get("Item-id-4"))->setCustomName($this->getConfig()->get("Palm-Island-Item-Name"));
		$item5 = Item::get($this->getConfig()->get("Item-id-5"))->setCustomName($this->getConfig()->get("Shelly-Island-Item-Name"));
		$item6 = Item::get($this->getConfig()->get("Item-id-6"))->setCustomName($this->getConfig()->get("Op-Island-Item-Name"));
		$item7 = Item::get($this->getConfig()->get("Item-id-7"))->setCustomName($this->getConfig()->get("Lost-Island-Item-Name"));
		$inv->setItem(9, $item3);
		$inv->setItem(11, $item4);
		$inv->setItem(13, $item5);
		$inv->setItem(15, $item6);
		$inv->setItem(17, $item7);
		$menu->send($player);
	}

	public function iscreate2(InvMenuTransaction $action) : InvMenuTransactionResult{
		$item = $action->getOut();
		$player = $action->getPlayer();
		$itemClicked = $item;
		if($item->getId() == ($this->getConfig()->get("Item-id-3"))){
			$action->getAction()->getInventory()->onClose($player);
			\pocketmine\Server::getInstance()->dispatchCommand($player, "is create");
			return $action->discard();
		}
		if($item->getId() == ($this->getConfig()->get("Item-id-4"))){
			$action->getAction()->getInventory()->onClose($player);
			\pocketmine\Server::getInstance()->dispatchCommand($player, "is create Palm");
			return $action->discard();
		}
		if($item->getId() == ($this->getConfig()->get("Item-id-5"))){
			$action->getAction()->getInventory()->onClose($player);
			\pocketmine\Server::getInstance()->dispatchCommand($player, "is create Shelly");
			return $action->discard();
		}
		if($item->getId() == ($this->getConfig()->get("Item-id-6"))){
			$action->getAction()->getInventory()->onClose($player);
			\pocketmine\Server::getInstance()->dispatchCommand($player, "is create Op");
			return $action->discard();
		}
		if($item->getId() == ($this->getConfig()->get("Item-id-7"))){
			$action->getAction()->getInventory()->onClose($player);
			\pocketmine\Server::getInstance()->dispatchCommand($player, "is create Lost");
			return $action->discard();
		}
		return $action->discard();
	}

	public function islandManagement(Player $player){
		$menu = InvMenu::create(InvMenu::TYPE_CHEST);
		$menu->readOnly();
		$menu->setListener(\Closure::fromCallable([$this, "ismanage"]));
		$menu->setName($this->getConfig()->get("Island-Management-Menu-Name"));
		$inv = $menu->getInventory();
		$item8 = Item::get($this->getConfig()->get("Item-id-8"))->setCustomName($this->getConfig()->get("Manage-Members-Item-Name"));
		$item9 = Item::get($this->getConfig()->get("Item-id-9"))->setCustomName($this->getConfig()->get("Manage-Island-Item-Name"));
		$item10 = Item::get($this->getConfig()->get("Item-id-10"))->setCustomName($this->getConfig()->get("Remove-Island-Item-Name"));
		$inv->setItem(10, $item8);
		$inv->setItem(13, $item9);
		$inv->setItem(16, $item10);
		$menu->send($player);
	}

	public function ismanage(InvMenuTransaction $action) : InvMenuTransactionResult{
		$item = $action->getOut();
		$player = $action->getPlayer();
		$itemClicked = $item;
		if($item->getId() == ($this->getConfig()->get("Item-id-8"))){
			$inv = $action->getAction()->getInventory();
			$inv->onClose($player);
			return $action->discard()->then(function(Player $player) : void{
			         $this->ismanagemembers($player);
			});
		}
		if($item->getId() == ($this->getConfig()->get("Item-id-9"))){
			$inv = $action->getAction()->getInventory();
			$inv->onClose($player);
			return $action->discard()->then(function(Player $player) : void{
			         $this->ismanageisland($player);
			});
		}
		if($item->getId() == ($this->getConfig()->get("Item-id-10"))){
			$action->getAction()->getInventory()->onClose($player);
			\pocketmine\Server::getInstance()->dispatchCommand($player, "is disband");
			return $action->discard();
		}
    }
}

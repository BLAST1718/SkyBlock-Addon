<?php

namespace TheBlast\SkyBlockGui;

use libs\jojoe77777\FormApi\CustomForm;
use libs\muqsit\invmenu\InvMenu;
use libs\muqsit\invmenu\InvMenuHandler;
use libs\muqsit\invmenu\transaction\InvMenuTransaction;
use libs\muqsit\invmenu\transaction\InvMenuTransactionResult;
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
		$item1 = Item::get($this->getConfig()->get("Item-id-1"))->setCustomName($this->getConfig()->get("Create-Island-Item-Name"));
		$item2 = Item::get($this->getConfig()->get("Item-id-2"))->setCustomName($this->getConfig()->get("Invite-Manage-Item-Name"));
		$inv->setItem($this->getConfig()->get("Item-Slot-1"), $item1);
		$inv->setItem($this->getConfig()->get("Item-Slot-2"), $item2);
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
			$action->getAction()->getInventory()->onClose($player);
			\pocketmine\Server::getInstance()->dispatchCommand($player, "is accept");
			return $action->discard();
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
		$inv->setItem($this->getConfig()->get("Item-Slot-3"), $item3);
		$inv->setItem($this->getConfig()->get("Item-Slot-4"), $item4);
		$inv->setItem($this->getConfig()->get("Item-Slot-5"), $item5);
		$inv->setItem($this->getConfig()->get("Item-Slot-6"), $item6);
		$inv->setItem($this->getConfig()->get("Item-Slot-7"), $item7);
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
		$inv->setItem($this->getConfig()->get("Item-Slot-8"), $item8);
		$inv->setItem($this->getConfig()->get("Item-Slot-9"), $item9);
		$inv->setItem($this->getConfig()->get("Item-Slot-10"), $item10);
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

    public function ismanagemembers(Player $player){
		$menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
		$menu->readOnly();
		$menu->setListener(\Closure::fromCallable([$this, "ismanagemember"]));
		$menu->setName($this->getConfig()->get("Management-Members-Menu-Name"));
		$inv = $menu->getInventory();
		$item11 = Item::get($this->getConfig()->get("Item-id-11"))->setCustomName($this->getConfig()->get("Cooperate-Members-Item-Name"));
		$item12 = Item::get($this->getConfig()->get("Item-id-12"))->setCustomName($this->getConfig()->get("Promote-Members-Item-Name"));
		$item13 = Item::get($this->getConfig()->get("Item-id-13"))->setCustomName($this->getConfig()->get("Demote-Members-Item-Name"));
		$item14 = Item::get($this->getConfig()->get("Item-id-14"))->setCustomName($this->getConfig()->get("Banish-Members-Item-Name"));
		$item15 = Item::get($this->getConfig()->get("Item-id-15"))->setCustomName($this->getConfig()->get("Fire-Members-Item-Name"));
		$item16 = Item::get($this->getConfig()->get("Item-id-16"))->setCustomName($this->getConfig()->get("Members-Members-Item-Name"));
		$inv->setItem($this->getConfig()->get("Item-Slot-11"), $item11);
		$inv->setItem($this->getConfig()->get("Item-Slot-12"), $item12);
		$inv->setItem($this->getConfig()->get("Item-Slot-13"), $item13);
		$inv->setItem($this->getConfig()->get("Item-Slot-14"), $item14);
		$inv->setItem($this->getConfig()->get("Item-Slot-15"), $item15);
		$inv->setItem($this->getConfig()->get("Item-Slot-16"), $item16);
		$menu->send($player);
	}

	public function ismanagemember(InvMenuTransaction $action) : InvMenuTransactionResult{
		$item = $action->getOut();
		$player = $action->getPlayer();
		$itemClicked = $item;
		if($item->getId() == ($this->getConfig()->get("Item-id-11"))){
			$inv = $action->getAction()->getInventory();
			$inv->onClose($player);
			return $action->discard()->then(function(Player $player) : void{
			         $this->ismemberspromote($player);
			});
		}
		if($item->getId() == ($this->getConfig()->get("Item-id-12"))){
			$inv = $action->getAction()->getInventory();
			$inv->onClose($player);
			return $action->discard()->then(function(Player $player) : void{
			         $this->ismembersdemote($player);
			});
		}
		if($item->getId() == ($this->getConfig()->get("Item-id-13"))){
			$inv = $action->getAction()->getInventory();
			$inv->onClose($player);
			return $action->discard()->then(function(Player $player) : void{
			         $this->ismemberscooperate($player);
			});
		}
		if($item->getId() == ($this->getConfig()->get("Item-id-14"))){
			$inv = $action->getAction()->getInventory();
			$inv->onClose($player);
			return $action->discard()->then(function(Player $player) : void{
			         $this->ismembersbanish($player);
			});
		}
		if($item->getId() == ($this->getConfig()->get("Item-id-15"))){
			$inv = $action->getAction()->getInventory();
			$inv->onClose($player);
			return $action->discard()->then(function(Player $player) : void{
			         $this->ismembersfire($player);
			});
		}
		if($item->getId() == ($this->getConfig()->get("Item-id-16"))){
			$action->getAction()->getInventory()->onClose($player);
			\pocketmine\Server::getInstance()->dispatchCommand($player, "is members");
			return $action->discard();
		}
    }

    public function ismanageisland(Player $player){
		$menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
		$menu->readOnly();
		$menu->setListener(\Closure::fromCallable([$this, "ismanageislan"]));
		$menu->setName($this->getConfig()->get("Management-Island-Menu-Name"));
		$inv = $menu->getInventory();
		$item17 = Item::get($this->getConfig()->get("Item-id-17"))->setCustomName($this->getConfig()->get("Join-Island-Item-Name"));
		$item18 = Item::get($this->getConfig()->get("Item-id-18"))->setCustomName($this->getConfig()->get("Lock-Island-Item-Name"));
		$item19 = Item::get($this->getConfig()->get("Item-id-19"))->setCustomName($this->getConfig()->get("Chat-Island-Item-Name"));
		$item20 = Item::get($this->getConfig()->get("Item-id-20"))->setCustomName($this->getConfig()->get("Setspawn-Island-Item-Name"));
		$item21 = Item::get($this->getConfig()->get("Item-id-21"))->setCustomName($this->getConfig()->get("Category-Island-Item-Name"));
		$item22 = Item::get($this->getConfig()->get("Item-id-22"))->setCustomName($this->getConfig()->get("Blocks-Island-Item-Name"));
		$inv->setItem($this->getConfig()->get("Item-Slot-17"), $item17);
		$inv->setItem($this->getConfig()->get("Item-Slot-18"), $item18);
		$inv->setItem($this->getConfig()->get("Item-Slot-19"), $item19);
		$inv->setItem($this->getConfig()->get("Item-Slot-20"), $item20);
		$inv->setItem($this->getConfig()->get("Item-Slot-21"), $item21);
		$inv->setItem($this->getConfig()->get("Item-Slot-22"), $item22);
		$menu->send($player);
	}

	public function ismanageislan(InvMenuTransaction $action) : InvMenuTransactionResult{
		$item = $action->getOut();
		$player = $action->getPlayer();
		$itemClicked = $item;
		if($item->getId() == ($this->getConfig()->get("Item-id-17"))){
			$action->getAction()->getInventory()->onClose($player);
			\pocketmine\Server::getInstance()->dispatchCommand($player, "is join");
			return $action->discard();
		}
		if($item->getId() == ($this->getConfig()->get("Item-id-18"))){
			$action->getAction()->getInventory()->onClose($player);
			\pocketmine\Server::getInstance()->dispatchCommand($player, "is lock");
			return $action->discard();
		}
		if($item->getId() == ($this->getConfig()->get("Item-id-19"))){
			$action->getAction()->getInventory()->onClose($player);
			\pocketmine\Server::getInstance()->dispatchCommand($player, "is chat");
			return $action->discard();
		}
		if($item->getId() == ($this->getConfig()->get("Item-id-20"))){
			$action->getAction()->getInventory()->onClose($player);
			\pocketmine\Server::getInstance()->dispatchCommand($player, "is setspawn");
			return $action->discard();
		}
		if($item->getId() == ($this->getConfig()->get("Item-id-21"))){
			$action->getAction()->getInventory()->onClose($player);
			\pocketmine\Server::getInstance()->dispatchCommand($player, "is category");
			return $action->discard();
		}
		if($item->getId() == ($this->getConfig()->get("Item-id-22"))){
			$action->getAction()->getInventory()->onClose($player);
			\pocketmine\Server::getInstance()->dispatchCommand($player, "is blocks");
			return $action->discard();
		}
    }
}

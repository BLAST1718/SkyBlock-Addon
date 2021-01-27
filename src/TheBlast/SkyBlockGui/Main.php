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
                $this->saveDefaultConfig();
                $this->getResources("config.yml");
		$this->getLogger()->info("enabled");
		$api = SkyBlock::getInstance();
		if(!InvMenuHandler::isRegistered()){
			InvMenuHandler::register($this);
		}
		$command = new PluginCommand("is", $this);
		$command->setDescription("Skyblock Menu");
		$this->getServer()->getCommandMap()->register("is", $command);
	}

	public function onDisable(){
		$this->getLogger()->info("disabled");
	}

	public function onCommand(CommandSender $player, Command $cmd, string $label, array $args) : bool{
        switch($cmd->getName()){
            case "is":
                if(!$player instanceof Player){
                    $player->sendMessage("SkyBlockGui");
                    return true;
                }
		$session = SessionLocator::getSession($player);
                if (!$session->hasIsland()) {
                        $this->islandManagement($player, $session);
                    } else {
                        $this->islandCreation($player, $session);
                }
                    break;
        }
        return true;
    }

	public function islandCreation(Player $player){
		$menu = InvMenu::create(InvMenu::TYPE_CHEST);
		$menu->readOnly();
		$menu->setListener(\Closure::fromCallable([$this, "iscreate"]));
		$menu->setName("Island Create");
		$inv = $menu->getInventory();
		$grass = Item::get(2)->setCustomName("§r§aCreate Island");
		$stone = Item::get(1)->setCustomName("§r§aAccept Invite");
		$inv->setItem(10, $grass);
		$inv->setItem(16, $stone);
		$menu->send($player);
	}

	public function iscreate(InvMenuTransaction $action) : InvMenuTransactionResult{
		$item = $action->getOut();
		$player = $action->getPlayer();
		$itemClicked = $item;
		if($item->getCustomName() === "§r§aCreate Island"){
			$inv = $action->getAction()->getInventory();
			$inv->onClose($player);
			return $action->discard()->then(function(Player $player) : void{
			         $this->IslandCreate2($player);
			});
		}
		if($item->getCustomName() === "§r§aAccept Invite"){
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
		$menu->setName("Choose Island");
		$inv = $menu->getInventory();
		$grass = Item::get(2)->setCustomName("§r§aBasic Island");
		$sand = Item::get(12)->setCustomName("§r§aPalm Island");
		$inv->setItem(10, $grass);
		$inv->setItem(16, $sand);
		$menu->send($player);
	}

	public function iscreate2(InvMenuTransaction $action) : InvMenuTransactionResult{
		$item = $action->getOut();
		$player = $action->getPlayer();
		$itemClicked = $item;
		if($item->getCustomName() === "§r§aBasic Island"){
			$action->getAction()->getInventory()->onClose($player);
			\pocketmine\Server::getInstance()->dispatchCommand($player, "is create");
			return $action->discard();
		}
		if($item->getCustomName() === "§r§aPalm Island"){
			$action->getAction()->getInventory()->onClose($player);
			\pocketmine\Server::getInstance()->dispatchCommand($player, "is create Palm");
			return $action->discard();
		}
		return $action->discard();
	}

	public function islandManagement(Player $player){
		$menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
		$menu->readOnly();
		$menu->setListener(\Closure::fromCallable([$this, "ismanage"]));
		$menu->setName("Management");
		$inv = $menu->getInventory();
		$skull = Item::get(397, 3)->setCustomName("§r§aManage Members");
		$grass = Item::get(1)->setCustomName("§r§aManage Island");
		$inv->setItem(10, $skull);
		$inv->setItem(16, $grass);
		$menu->send($player);
	}

	public function ismanage(InvMenuTransaction $action) : InvMenuTransactionResult{
		$item = $action->getOut();
		$player = $action->getPlayer();
		$itemClicked = $item;
		if($item->getCustomName() === "§r§aManage Members"){
			$inv = $action->getAction()->getInventory();
			$inv->onClose($player);
			return $action->discard()->then(function(Player $player) : void{
			         $this->ismanagemembers($player);
			});
		}
		if($item->getCustomName() === "§r§aManage Island"){
			$inv = $action->getAction()->getInventory();
			$inv->onClose($player);
			return $action->discard()->then(function(Player $player) : void{
			         $this->ismanageisland($player);
			});
		}
    }
}

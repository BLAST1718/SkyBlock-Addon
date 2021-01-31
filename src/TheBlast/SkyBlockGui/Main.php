<?php

# ____    _  __ __   __  ____    _        ___     ____   _  __ 
#/ ___|  | |/ / \ \ / / | __ )  | |      / _ \   / ___| | |/ / 
#\___ \  | ' /   \ V /  |  _ \  | |     | | | | | |     | ' /  
# ___) | | . \    | |   | |_) | | |___  | |_| | | |___  | . \  
#|____/  |_|\_\   |_|   |____/  |_____|  \___/   \____| |_|\_\
#
#
#               ____   _   _   ___      __  _   _   ___  
#              / ___| | | | | |_ _|    / / | | | | |_ _| 
#             | |  _  | | | |  | |    / /  | | | |  | |  
#             | |_| | | |_| |  | |   / /   | |_| |  | |  
#              \____|  \___/  |___| /_/     \___/  |___|
#
#                        _               
#                       | |__    _   _  
#                       | '_ \  | | | | 
#                       | |_) | | |_| | 
#                       |_.__/   \__, | 
#                                 |___/
#
# _____                  ____    _                 _    
#|_   _| | |__     ___  | __ )  | |   __ _   ___  | |_    
#  | |   | '_ \   / _ \ |  _ \  | |  / _` | / __| | __| 
#  | |   | | | | |  __/ | |_) | | | | (_| | \__ \ | |_  
#  |_|   |_| |_|  \___| |____/  |_|  \__,_| |___/  \__|

namespace TheBlast\SkyBlockGui;

use jojoe77777\FormApi\CustomForm;
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
                        $this->islandCreation($this->getConfig()->get("Option")($player, $session));
                    } else {
                        $this->islandManagement($this->getConfig()->get("Option")($player, $session));
                }
                    break;
        }
        return true;
    }

#      ____   _   _   ___  
#     / ___| | | | | |_ _| 
#    | |  _  | | | |  | |  
#    | |_| | | |_| |  | |  
#     \____|  \___/  |___|

    public function islandCreationGui(Player $player){
      $menu = InvMenu::create(InvMenu::TYPE_CHEST);
      $menu->readOnly();
      $menu->setListener(\Closure::fromCallable([$this, "iscreategui"]));
      $menu->setName($this->getConfig()->get("Island-Creation-Gui-Menu-Name"));
      $inv = $menu->getInventory();
      $item1 = Item::get($this->getConfig()->get("Item-id-1"), $this->getConfig()->get("Item-meta-1"), 1)->setCustomName($this->getConfig()->get("Create-Island-Gui-Item-Name"));
      $item2 = Item::get($this->getConfig()->get("Item-id-2"), $this->getConfig()->get("Item-meta-2"), 1)->setCustomName($this->getConfig()->get("Accept-Invite-Gui-Item-Name"));
      $inv->setItem($this->getConfig()->get("Item-Slot-1"), $item1);
      $inv->setItem($this->getConfig()->get("Item-Slot-2"), $item2);
      $menu->send($player);
  }

   public function iscreategui(InvMenuTransaction $action) : InvMenuTransactionResult{
      $item = $action->getOut();
      $player = $action->getPlayer();
      $itemClicked = $item;
      if($item->getCustomName() ==  $this->getConfig()->get("Create-Island-Gui-Item-Name")){
         $inv = $action->getAction()->getInventory();
         $inv->onClose($player);
         return $action->discard()->then(function(Player $player) : void{
                  $this->IslandCreation2Gui($player);
         });
      }
      if($item->getCustomName() ==  $this->getConfig()->get("Accept-Invite-Gui-Item-Name")){
         $action->getAction()->getInventory()->onClose($player);
         \pocketmine\Server::getInstance()->dispatchCommand($player, "is accept");
         return $action->discard();
      }
      return $action->discard();
  }

   public function islandCreation2Gui(Player $player){
      $menu = InvMenu::create(InvMenu::TYPE_CHEST);
      $menu->readOnly();
      $menu->setListener(\Closure::fromCallable([$this, "iscreate2gui"]));
      $menu->setName($this->getConfig()->get("Choose-Island-Gui-Menu-Name"));
      $inv = $menu->getInventory();
      $item3 = Item::get($this->getConfig()->get("Item-id-3"), $this->getConfig()->get("Item-meta-3"), 1)->setCustomName($this->getConfig()->get("Basic-Island-Gui-Item-Name"));
      $item4 = Item::get($this->getConfig()->get("Item-id-4"), $this->getConfig()->get("Item-meta-4"), 1)->setCustomName($this->getConfig()->get("Palm-Island-Gui-Item-Name"));
      $item5 = Item::get($this->getConfig()->get("Item-id-5"), $this->getConfig()->get("Item-meta-5"), 1)->setCustomName($this->getConfig()->get("Shelly-Island-Gui-Item-Name"));
      $item6 = Item::get($this->getConfig()->get("Item-id-6"), $this->getConfig()->get("Item-meta-6"), 1)->setCustomName($this->getConfig()->get("Op-Island-Item-Gui-Name"));
      $item7 = Item::get($this->getConfig()->get("Item-id-7"), $this->getConfig()->get("Item-meta-7"), 1)->setCustomName($this->getConfig()->get("Lost-Island-Item-Gui-Name"));
      $inv->setItem($this->getConfig()->get("Item-Slot-3"), $item3);
      $inv->setItem($this->getConfig()->get("Item-Slot-4"), $item4);
      $inv->setItem($this->getConfig()->get("Item-Slot-5"), $item5);
      $inv->setItem($this->getConfig()->get("Item-Slot-6"), $item6);
      $inv->setItem($this->getConfig()->get("Item-Slot-7"), $item7);
      $menu->send($player);
   }

   public function iscreate2gui(InvMenuTransaction $action) : InvMenuTransactionResult{
      $item = $action->getOut();
      $player = $action->getPlayer();
      $itemClicked = $item;
      if($item->getCustomName() ==  $this->getConfig()->get("Basic-Island-Gui-Item-Name")){
         $action->getAction()->getInventory()->onClose($player);
         \pocketmine\Server::getInstance()->dispatchCommand($player, "is create");
         return $action->discard();
      }
      if($item->getCustomName() ==  $this->getConfig()->get("Palm-Island-Gui-Item-Name")){
         $action->getAction()->getInventory()->onClose($player);
         \pocketmine\Server::getInstance()->dispatchCommand($player, "is create Palm");
         return $action->discard();
      }
      if($item->getCustomName() ==  $this->getConfig()->get("Shelly-Island-Gui-Item-Name")){
         $action->getAction()->getInventory()->onClose($player);
         \pocketmine\Server::getInstance()->dispatchCommand($player, "is create Shelly");
         return $action->discard();
      }
      if($item->getCustomName() ==  $this->getConfig()->get("Op-Island-Gui-Item-Name")){
         $action->getAction()->getInventory()->onClose($player);
         \pocketmine\Server::getInstance()->dispatchCommand($player, "is create Op");
         return $action->discard();
      }
      if($item->getCustomName() ==  $this->getConfig()->get("Lost-Island-Gui-Item-Name")){
         $action->getAction()->getInventory()->onClose($player);
         \pocketmine\Server::getInstance()->dispatchCommand($player, "is create Lost");
         return $action->discard();
      }
      return $action->discard();
   }

   public function islandManagementGui(Player $player){
      $menu = InvMenu::create(InvMenu::TYPE_CHEST);
      $menu->readOnly();
      $menu->setListener(\Closure::fromCallable([$this, "ismanagegui"]));
      $menu->setName($this->getConfig()->get("Island-Management-Gui-Menu-Name"));
      $inv = $menu->getInventory();
      $item8 = Item::get($this->getConfig()->get("Item-id-8"), $this->getConfig()->get("Item-meta-8"), 1)->setCustomName($this->getConfig()->get("Manage-Members-Gui-Item-Name"));
      $item9 = Item::get($this->getConfig()->get("Item-id-9"), $this->getConfig()->get("Item-meta-9"), 1)->setCustomName($this->getConfig()->get("Manage-Island-Gui-Item-Name"));
      $item10 = Item::get($this->getConfig()->get("Item-id-10"), $this->getConfig()->get("Item-meta-10"), 1)->setCustomName($this->getConfig()->get("Warning-Area-Island-Gui-Item-Name"));
      $inv->setItem($this->getConfig()->get("Item-Slot-8"), $item8);
      $inv->setItem($this->getConfig()->get("Item-Slot-9"), $item9);
      $inv->setItem($this->getConfig()->get("Item-Slot-10"), $item10);
      $menu->send($player);
   }

   public function ismanagegui(InvMenuTransaction $action) : InvMenuTransactionResult{
      $item = $action->getOut();
      $player = $action->getPlayer();
      $itemClicked = $item;
      if($item->getCustomName() ==  $this->getConfig()->get("Manage-Members-Gui-Item-Name")){
         $inv = $action->getAction()->getInventory();
         $inv->onClose($player);
         return $action->discard()->then(function(Player $player) : void{
                  $this->ismanagemembersgui($player);
         });
      }
      if($item->getCustomName() ==  $this->getConfig()->get("Manage-Island-Gui-Item-Name")){
         $inv = $action->getAction()->getInventory();
         $inv->onClose($player);
         return $action->discard()->then(function(Player $player) : void{
                  $this->ismanageislandgui($player);
         });
      }
      if($item->getCustomName() ==  $this->getConfig()->get("Warning-Area-Island-Gui-Item-Name")){
         $inv = $action->getAction()->getInventory();
         $inv->onClose($player);
         return $action->discard()->then(function(Player $player) : void{
                  $this->ismanagewarninggui($player);
         });
      }
   }

   public function ismanagemembersgui(Player $player){
      $menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
      $menu->readOnly();
      $menu->setListener(\Closure::fromCallable([$this, "ismanagemembergui"]));
      $menu->setName($this->getConfig()->get("Management-Members-Gui-Menu-Name"));
      $inv = $menu->getInventory();
      $item11 = Item::get($this->getConfig()->get("Item-id-11"), $this->getConfig()->get("Item-meta-11"), 1)->setCustomName($this->getConfig()->get("Cooperate-Members-Gui-Item-Name"));
      $item12 = Item::get($this->getConfig()->get("Item-id-12"), $this->getConfig()->get("Item-meta-12"), 1)->setCustomName($this->getConfig()->get("Promote-Members-Gui-Item-Name"));
      $item13 = Item::get($this->getConfig()->get("Item-id-13"), $this->getConfig()->get("Item-meta-13"), 1)->setCustomName($this->getConfig()->get("Demote-Members-Gui-Item-Name"));
      $item14 = Item::get($this->getConfig()->get("Item-id-14"), $this->getConfig()->get("Item-meta-14"), 1)->setCustomName($this->getConfig()->get("Banish-Members-Gui-Item-Name"));
      $item15 = Item::get($this->getConfig()->get("Item-id-15"), $this->getConfig()->get("Item-meta-15"), 1)->setCustomName($this->getConfig()->get("Fire-Members-Gui-Item-Name"));
      $item16 = Item::get($this->getConfig()->get("Item-id-16"), $this->getConfig()->get("Item-meta-16"), 1)->setCustomName($this->getConfig()->get("Members-Members-Gui-Item-Name"));
      $inv->setItem($this->getConfig()->get("Item-Slot-11"), $item11);
      $inv->setItem($this->getConfig()->get("Item-Slot-12"), $item12);
      $inv->setItem($this->getConfig()->get("Item-Slot-13"), $item13);
      $inv->setItem($this->getConfig()->get("Item-Slot-14"), $item14);
      $inv->setItem($this->getConfig()->get("Item-Slot-15"), $item15);
      $inv->setItem($this->getConfig()->get("Item-Slot-16"), $item16);
      $menu->send($player);
   }

   public function ismanagemembergui(InvMenuTransaction $action) : InvMenuTransactionResult{
      $item = $action->getOut();
      $player = $action->getPlayer();
      $itemClicked = $item;
      if($item->getCustomName() ==  $this->getConfig()->get("Cooperate-Members-Gui-Item-Name")){
         $inv = $action->getAction()->getInventory();
         $inv->onClose($player);
         return $action->discard()->then(function(Player $player) : void{
                  $this->ismemberscooperate($player);
         });
      }
      if($item->getCustomName() ==  $this->getConfig()->get("Promote-Members-Gui-Item-Name")){
         $inv = $action->getAction()->getInventory();
         $inv->onClose($player);
         return $action->discard()->then(function(Player $player) : void{
                  $this->ismemberspromote($player);
         });
      }
      if($item->getCustomName() ==  $this->getConfig()->get("Demote-Members-Gui-Item-Name")){
         $inv = $action->getAction()->getInventory();
         $inv->onClose($player);
         return $action->discard()->then(function(Player $player) : void{
                  $this->ismembersdemote($player);
         });
      }
      if($item->getCustomName() ==  $this->getConfig()->get("Banish-Members-Gui-Item-Name")){
         $inv = $action->getAction()->getInventory();
         $inv->onClose($player);
         return $action->discard()->then(function(Player $player) : void{
                  $this->ismembersbanish($player);
         });
      }
      if($item->getCustomName() ==  $this->getConfig()->get("Fire-Members-Gui-Item-Name")){
         $inv = $action->getAction()->getInventory();
         $inv->onClose($player);
         return $action->discard()->then(function(Player $player) : void{
                  $this->ismembersfire($player);
         });
      }
      if($item->getCustomName() ==  $this->getConfig()->get("Members-Members-Gui-Item-Name")){
         $action->getAction()->getInventory()->onClose($player);
         \pocketmine\Server::getInstance()->dispatchCommand($player, "is members");
         return $action->discard();
      }
   }

   public function ismanageislandgui(Player $player){
      $menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
      $menu->readOnly();
      $menu->setListener(\Closure::fromCallable([$this, "ismanageislangui"]));
      $menu->setName($this->getConfig()->get("Management-Island-Gui-Menu-Name"));
      $inv = $menu->getInventory();
      $item17 = Item::get($this->getConfig()->get("Item-id-17"), $this->getConfig()->get("Item-meta-17"), 1)->setCustomName($this->getConfig()->get("Join-Island-Gui-Item-Name"));
      $item18 = Item::get($this->getConfig()->get("Item-id-18"), $this->getConfig()->get("Item-meta-18"), 1)->setCustomName($this->getConfig()->get("Lock-Island-Gui-Item-Name"));
      $item19 = Item::get($this->getConfig()->get("Item-id-19"), $this->getConfig()->get("Item-meta-19"), 1)->setCustomName($this->getConfig()->get("Chat-Island-Gui-Item-Name"));
      $item20 = Item::get($this->getConfig()->get("Item-id-20"), $this->getConfig()->get("Item-meta-20"), 1)->setCustomName($this->getConfig()->get("Setspawn-Island-Gui-Item-Name"));
      $item21 = Item::get($this->getConfig()->get("Item-id-21"), $this->getConfig()->get("Item-meta-21"), 1)->setCustomName($this->getConfig()->get("Category-Island-Gui-Item-Name"));
      $item22 = Item::get($this->getConfig()->get("Item-id-22"), $this->getConfig()->get("Item-meta-22"), 1)->setCustomName($this->getConfig()->get("Blocks-Island-Gui-Item-Name"));
      $item23 = Item::get($this->getConfig()->get("Item-id-23"), $this->getConfig()->get("Item-meta-23"), 1)->setCustomName($this->getConfig()->get("Visit-Island-Gui-Item-Name"));
      $item24 = Item::get($this->getConfig()->get("Item-id-24"), $this->getConfig()->get("Item-meta-24"), 1)->setCustomName($this->getConfig()->get("Help-Island-Gui-Name"));
      $inv->setItem($this->getConfig()->get("Item-Slot-17"), $item17);
      $inv->setItem($this->getConfig()->get("Item-Slot-18"), $item18);
      $inv->setItem($this->getConfig()->get("Item-Slot-19"), $item19);
      $inv->setItem($this->getConfig()->get("Item-Slot-20"), $item20);
      $inv->setItem($this->getConfig()->get("Item-Slot-21"), $item21);
      $inv->setItem($this->getConfig()->get("Item-Slot-22"), $item22);
      $inv->setItem($this->getConfig()->get("Item-Slot-23"), $item23);
      $inv->setItem($this->getConfig()->get("Item-Slot-24"), $item24);
      $menu->send($player);
   }

   public function ismanageislangui(InvMenuTransaction $action) : InvMenuTransactionResult{
      $item = $action->getOut();
      $player = $action->getPlayer();
      $itemClicked = $item;
      if($item->getCustomName() ==  $this->getConfig()->get("Join-Island-Gui-Item-Name")){
         $action->getAction()->getInventory()->onClose($player);
         \pocketmine\Server::getInstance()->dispatchCommand($player, "is join");
         return $action->discard();
      }
      if($item->getCustomName() ==  $this->getConfig()->get("Lock-Island-Gui-Item-Name")){
         $action->getAction()->getInventory()->onClose($player);
         \pocketmine\Server::getInstance()->dispatchCommand($player, "is lock");
         return $action->discard();
      }
      if($item->getCustomName() ==  $this->getConfig()->get("Chat-Island-Gui-Item-Name")){
         $action->getAction()->getInventory()->onClose($player);
         \pocketmine\Server::getInstance()->dispatchCommand($player, "is chat");
         return $action->discard();
      }
      if($item->getCustomName() ==  $this->getConfig()->get("Setspawn-Island-Gui-Item-Name")){
         $action->getAction()->getInventory()->onClose($player);
         \pocketmine\Server::getInstance()->dispatchCommand($player, "is setspawn");
         return $action->discard();
      }
      if($item->getCustomName() ==  $this->getConfig()->get("Category-Island-Gui-Item-Name")){
         $action->getAction()->getInventory()->onClose($player);
         \pocketmine\Server::getInstance()->dispatchCommand($player, "is category");
         return $action->discard();
      }
      if($item->getCustomName() ==  $this->getConfig()->get("Blocks-Island-Gui-Item-Name")){
         $action->getAction()->getInventory()->onClose($player);
         \pocketmine\Server::getInstance()->dispatchCommand($player, "is blocks");
         return $action->discard();
      }
      if($item->getCustomName() ==  $this->getConfig()->get("Visit-Island-Gui-Item-Name")){
         $inv = $action->getAction()->getInventory();
         $inv->onClose($player);
         return $action->discard()->then(function(Player $player) : void{
                  $this->isvisit($player);
         });
      }
      if($item->getCustomName() ==  $this->getConfig()->get("Help-Island-Gui-Item-Name")){
         $action->getAction()->getInventory()->onClose($player);
         \pocketmine\Server::getInstance()->dispatchCommand($player, "is help");
         return $action->discard();
      }
   }

   public function ismanagewarninggui(Player $player){
      $menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
      $menu->readOnly();
      $menu->setListener(\Closure::fromCallable([$this, "ismanagewarningui"]));
      $menu->setName($this->getConfig()->get("Management-Warning-Gui-Menu-Name"));
      $inv = $menu->getInventory();
      $item25 = Item::get($this->getConfig()->get("Item-id-25"), $this->getConfig()->get("Item-meta-25"), 1)->setCustomName($this->getConfig()->get("Transfer-Warning-Gui-Item-Name"));
      $item26 = Item::get($this->getConfig()->get("Item-id-26"), $this->getConfig()->get("Item-meta-26"), 1)->setCustomName($this->getConfig()->get("Disband-Warning-Gui-Item-Name"));
      $inv->setItem($this->getConfig()->get("Item-Slot-25"), $item25);
      $inv->setItem($this->getConfig()->get("Item-Slot-26"), $item26);
      $menu->send($player);
   }

   public function ismanagewarningui(InvMenuTransaction $action) : InvMenuTransactionResult{
      $item = $action->getOut();
      $player = $action->getPlayer();
      $itemClicked = $item;
      if($item->getCustomName() ==  $this->getConfig()->get("Transfer-Warning-Gui-Item-Name")){
         $inv = $action->getAction()->getInventory();
         $inv->onClose($player);
         return $action->discard()->then(function(Player $player) : void{
                  $this->iswarningtransfer($player);
         });
      }
      if($item->getCustomName() ==  $this->getConfig()->get("Disband-Warning-Gui-Item-Name")){
         $action->getAction()->getInventory()->onClose($player);
         \pocketmine\Server::getInstance()->dispatchCommand($player, "is disband");
         return $action->discard();
      }
   }

#    _   _   ___  
#   | | | | |_ _| 
#   | | | |  | |  
#   | |_| |  | |  
#    \___/  |___|

   public function islandCreationUi(Player $player): void {
        $form = createSimpleForm(function (Player $player, $data) use ($session) {
            $result = $data;
            if ($result === null)
                return;

            switch ($result) {
                case 0:
                    $this->IslandCreation2Ui($player);
                    break;
                case 1:
                    \pocketmine\Server::getInstance()->dispatchCommand($player, "is accept");
                    break;
            }
        });
        $form->setTitle($this->getConfig()->get("Island-Creation-Ui-Menu-Name"));
        $form->setContent($this->getConfig()->get("Island-Creation-Ui-Content"));
        $form->addButton($this->getConfig()->get("Create-Island-Ui-Button-Name"));
        $form->addButton($this->getConfig()->get("Accept-Invite-Ui-Button-Name"));
        $player->sendForm($form);
    }


   public function islandCreation2Ui(Player $player, Session $session): void {
        $form = createSimpleForm(function (Player $player, $data) use ($session) {
            $result = $data;
            if ($result === null)
                return;

            switch ($result) {
                case 0:
                    \pocketmine\Server::getInstance()->dispatchCommand($player, "is create");
                    break;
                case 1:
                    \pocketmine\Server::getInstance()->dispatchCommand($player, "is create Palm");
                    break;
                case 2:
                    \pocketmine\Server::getInstance()->dispatchCommand($player, "is create Op");
                    break;
                case 3:
                    \pocketmine\Server::getInstance()->dispatchCommand($player, "is create Shelly");
                    break;
                case 4:
                    \pocketmine\Server::getInstance()->dispatchCommand($player, "is create Lost"); 
                    break;
            }
        });
        $form->setTitle("Create Island");
        $form->setContent("Select an island to create!");
        $form->addButton("Basic");
        $form->addButton("Palm");
        $form->addButton("Op");
        $form->addButton("Shelly");
        $form->addButton("Lost");
        $player->sendForm($form);
    }

#     ___                           _     _        
#    |_ _|  _ __    _ __    _   _  | |_  ( )  ___  
#     | |  | '_ \  | '_ \  | | | | | __| |/  / __| 
#     | |  | | | | | |_) | | |_| | | |_      \__ \ 
#    |___| |_| |_| | .__/   \__,_|  \__|     |___/ 
#              |_|

    public function ismemberspromote($player){
      $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
         $form = $api->createCustomForm(function(Player $player, $result){
         if($result === null){
            return;
         }
         if(trim($result[0]) === ""){
            $player->sendActionbarMessage("§cPlease input name");
            return;
         }
         $this->getServer()->getCommandMap()->dispatch($player, "is promote ".$result[0]);
      });
      $form->setTitle("Promote Member");
      $form->addInput("Input the name");
      $player->sendForm($form);
      }

      public function ismembersdemote($player){
      $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
         $form = $api->createCustomForm(function(Player $player, $result){
         if($result === null){
            return;
         }
         if(trim($result[0]) === ""){
            $player->sendActionbarMessage("§cPlease input name");
            return;
         }
         $this->getServer()->getCommandMap()->dispatch($player, "is demote ".$result[0]);
      });
      $form->setTitle("Demote Member");
      $form->addInput("Input the name");
      $player->sendForm($form);
      }

      public function ismembersfire($player){
      $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
         $form = $api->createCustomForm(function(Player $player, $result){
         if($result === null){
            return;
         }
         if(trim($result[0]) === ""){
            $player->sendActionbarMessage("§cPlease input name");
            return;
         }
         $this->getServer()->getCommandMap()->dispatch($player, "is fire ".$result[0]);
      });
      $form->setTitle("Fire Member");
      $form->addInput("Input the name");
      $player->sendForm($form);
      }

      public function ismembersbanish($player){
      $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
         $form = $api->createCustomForm(function(Player $player, $result){
         if($result === null){
            return;
         }
         if(trim($result[0]) === ""){
            $player->sendActionbarMessage("§cPlease input name");
            return;
         }
         $this->getServer()->getCommandMap()->dispatch($player, "is banish ".$result[0]);
      });
      $form->setTitle("Banish Member");
      $form->addInput("Input the name");
      $player->sendForm($form);
      }

      public function ismemberscooperate($player){ 
      $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
         $form = $api->createCustomForm(function(Player $player, $result){
         if($result === null){
            return;
         }
         if(trim($result[0]) === ""){
            $player->sendActionbarMessage("§cPlease input name");
            return;
         }
         $this->getServer()->getCommandMap()->dispatch($player, "is cooperate ".$result[0]);
      });
      $form->setTitle("Cooperate Member");
      $form->addInput("Input the name");
      $player->sendForm($form);
      }

      public function isvisit($player){
      $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
         $form = $api->createCustomForm(function(Player $player, $result){
         if($result === null){
            return;
         }
         if(trim($result[0]) === ""){
            $player->sendActionbarMessage("§cPlease input name");
            return;
         }
         $this->getServer()->getCommandMap()->dispatch($player, "is visit ".$result[0]);
      });
      $form->setTitle("Visit Island");
      $form->addInput("Input the name");
      $player->sendForm($form);
      }

      public function isinviteinvite($player){
      $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
         $form = $api->createCustomForm(function(Player $player, $result){
         if($result === null){
            return;
         }
         if(trim($result[0]) === ""){
            $player->sendActionbarMessage("§cPlease input name");
            return;
         }
         $this->getServer()->getCommandMap()->dispatch($player, "is invite ".$result[0]);
      });
      $form->setTitle("Invite Player");
      $form->addInput("Input the name");
      $player->sendForm($form);
      }

      public function isinviteaccept($player){
      $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
         $form = $api->createCustomForm(function(Player $player, $result){
         if($result === null){
            return;
         }
         if(trim($result[0]) === ""){
            $player->sendActionbarMessage("§cPlease input name");
            return;
         }
         $this->getServer()->getCommandMap()->dispatch($player, "is accept ".$result[0]);
      });
      $form->setTitle("Accept Invite");
      $form->addInput("Input the name");
      $player->sendForm($form);
      }

      public function isinvitedeny($player){
      $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
         $form = $api->createCustomForm(function(Player $player, $result){
         if($result === null){
            return;
         }
         if(trim($result[0]) === ""){
            $player->sendActionbarMessage("§cPlease input name");
            return;
         }
         $this->getServer()->getCommandMap()->dispatch($player, "is deny ".$result[0]);
      });
      $form->setTitle("Deny Invite");
      $form->addInput("Input the name");
      $player->sendForm($form);
      }
}
}

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

namespace TheBlast\SkyBlock;

use jojoe77777\FormApi\CustomForm;
use jojoe77777\FormApi\SimpleForm;
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
      $api = SkyBlock::getInstance();
      if(!InvMenuHandler::isRegistered()){
         InvMenuHandler::register($this);
      }
      $command = new PluginCommand("sb1", $this);
      $command->setDescription("Skyblock Menu");
      $this->getServer()->getCommandMap()->register("sb1", $command);
   }


   public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
       switch($command->getName()) {
           case "sb1":
           if(!$sender instanceof Player) {
               $sender->sendMessage("Pls run this command ingame.");
               return true;
           }
           $session = SessionLocator::getSession($sender);

           if (!$session->hasIsland()) {
               if ($this->getConfig()->get("Option") == "ui") {
                   $this->islandCreationUi($sender);
               }else {
                   $this->islandCreationGui($sender);
               }
           }else {
               if ($this->getConfig()->get("Option") == "ui") {
                   $this->islandManagementUi($sender);
               }else {
                   $this->islandManagementGui($sender);
               }
           break;
       }
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
      $item27 = Item::get($this->getConfig()->get("Item-id-27"), $this->getConfig()->get("Item-meta-27"), 1)->setCustomName($this->getConfig()->get("Visit-Island-Gui-Item-Name"));
      $inv->setItem($this->getConfig()->get("Item-Slot-1"), $item1);
      $inv->setItem($this->getConfig()->get("Item-Slot-2"), $item2);
      $inv->setItem($this->getConfig()->get("Item-Slot-27"), $item27);
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
      if($item->getCustomName() ==  $this->getConfig()->get("Visit-Island-Gui-Item-Name")){
         $inv = $action->getAction()->getInventory();
         $inv->onClose($player);
         return $action->discard()->then(function(Player $player) : void{
                  $this->isvisit($player);
         });
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

   public function islandCreationUi($player){
                  $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
                  $form = $api->createSimpleForm(function (Player $player, int $data = null){
                           $result = $data;
                           if($result === null){
                                    return true;
                           }
                           switch ($result){
                                    //actions when button clicked
                                    case 0:
                                        $this->IslandCreation2Ui($player);
                                    break;

                                    case 1:
                                        $this->getServer()->dispatchCommand($player, "is accept");
                                    break;

                                    case 2:
                                        $this->isvisit($player);
                                    break;
                           }
                  });
       $form->setTitle($this->getConfig()->get("Island-Creation-Ui-Menu-Name"));
       $form->setContent($this->getConfig()->get("Island-Creation-Ui-Content"));
       $form->addButton($this->getConfig()->get("Create-Island-Ui-Button-Name"));
       $form->addButton($this->getConfig()->get("Accept-Invite-Ui-Button-Name"));
       $form->addButton($this->getConfig()->get("Visit-Island-Ui-Button-Name"));
       $player->sendForm($form);
       return $form;
         }

   public function islandCreation2Ui($player){
      $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
       $form = $api->createSimpleForm(function (Player $player, int $data = null){
            $result = $data;
            if($result === null){
            return true;
            }
               switch ($result){
               //actions when button clicked
               case 0;
               $this->getServer()->dispatchCommand($player, "is create");
               break;

               case 1;
               $this->getServer()->dispatchCommand($player, "is create Palm");
               break;

               case 2;
               $this->getServer()->dispatchCommand($player, "is create Op");
               break;

               case 3;
               $this->getServer()->dispatchCommand($player, "is create Shelly");
               break;

               case 4;
               $this->getServer()->dispatchCommand($player, "is create Lost");
               break;
                           }
                  });
       $form->setTitle($this->getConfig()->get("Island-Creation2-Ui-Menu-Name"));
       $form->setContent($this->getConfig()->get("Island-Creation2-Ui-Content"));
       $form->addButton($this->getConfig()->get("Basic-Island-Ui-Button-Name"));
       $form->addButton($this->getConfig()->get("Palm-Island-Ui-Button-Name"));
       $form->addButton($this->getConfig()->get("Op-Island-Ui-Button-Name"));
       $form->addButton($this->getConfig()->get("Shelly-Island-Ui-Button-Name"));
       $form->addButton($this->getConfig()->get("Lost-Island-Ui-Button-Name"));
       $player->sendForm($form);
       return $form;
      }

      public function islandManagementUi($player){
      $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
       $form = $api->createSimpleForm(function (Player $player, int $data = null){
            $result = $data;
            if($result === null){
            return true;
            }
               switch ($result){
               //actions when button clicked
               case 0;
               $this->islandManageMembers($player);
               break;

               case 1;
               $this->islandManageIsland($player);
               break;

               case 2;
               $this->islandManageWarning($player);
               break;

               case 3;
               $this->isvisit($player);
               break;

                           }
                  });
       $form->setTitle($this->getConfig()->get("Island-Management-Ui-Menu-Name"));
       $form->setContent($this->getConfig()->get("Island-Management-Ui-Content"));
       $form->addButton($this->getConfig()->get("Manage-Members-Ui-Button-Name"));
       $form->addButton($this->getConfig()->get("Manage-Island-Ui-Button-Name"));
       $form->addButton($this->getConfig()->get("Warning-Area-Island-Ui-Button-Name"));
       $form->addButton($this->getConfig()->get("Visit-Island-Ui-Button-Name"));
       $player->sendForm($form);
       return $form;
      }

      public function islandManageMembers($player){
      $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
       $form = $api->createSimpleForm(function (Player $player, int $data = null){
            $result = $data;
            if($result === null){
            return true;
            }
               switch ($result){
               //actions when button clicked
               case 0;
               $this->ismemberscooperate($player);
               break;

               case 1;
               $this->ismemberspromote($player);
               break;

               case 2;
               $this->ismembersdemote($player);
               break;

               case 3;
               $this->ismembersbanish($player);
               break;

               case 4;
               $this->ismembersfire($player);
               break;

               case 5;
               $this->getServer()->dispatchCommand($player, "is members");
               break;

                           }
                  });
       $form->setTitle($this->getConfig()->get("Members-Management-Ui-Menu-Name"));
       $form->setContent($this->getConfig()->get("Members-Management-Ui-Content"));
       $form->addButton($this->getConfig()->get("Cooperate-Members-Ui-Button-Name"));
       $form->addButton($this->getConfig()->get("Promote-Members-Ui-Button-Name"));
       $form->addButton($this->getConfig()->get("Demote-Members-Ui-Button-Name"));
       $form->addButton($this->getConfig()->get("Banish-Members-Ui-Button-Name"));
       $form->addButton($this->getConfig()->get("Fire-Members-Ui-Button-Name"));
       $form->addButton($this->getConfig()->get("Members-Members-Ui-Button-Name"));
       $player->sendForm($form);
       return $form;
      }

      public function islandManageIsland($player){
      $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
       $form = $api->createSimpleForm(function (Player $player, int $data = null){
            $result = $data;
            if($result === null){
            return true;
            }
               switch ($result){
               //actions when button clicked
               case 0;
               $this->getServer()->dispatchCommand($player, "is join");
               break;

               case 1;
               $this->getServer()->dispatchCommand($player, "is lock");
               break;

               case 2;
               $this->getServer()->dispatchCommand($player, "is chat");
               break;

               case 3;
               $this->getServer()->dispatchCommand($player, "is setspawn");
               break;

               case 4;
               $this->getServer()->dispatchCommand($player, "is category");
               break;

               case 5;
               $this->getServer()->dispatchCommand($player, "is blocks");
               break;

               case 6:
               $this->isvisit($player);
               break;

               case 7;
               $this->getServer()->dispatchCommand($player, "is help");
               break;

                           }
                  });
       $form->setTitle($this->getConfig()->get("Island-Management2-Ui-Menu-Name"));
       $form->setContent($this->getConfig()->get("Island-Management2-Ui-Content"));
       $form->addButton($this->getConfig()->get("Join-Island-Ui-Button-Name"));
       $form->addButton($this->getConfig()->get("Lock-Island-Ui-Button-Name"));
       $form->addButton($this->getConfig()->get("Chat-Island-Ui-Button-Name"));
       $form->addButton($this->getConfig()->get("Setspawn-Island-Ui-Button-Name"));
       $form->addButton($this->getConfig()->get("Category-Island-Ui-Button-Name"));
       $form->addButton($this->getConfig()->get("Blocks-Island-Ui-Button-Name"));
       $form->addButton($this->getConfig()->get("Visit-Island-Ui-Button-Name"));
       $form->addButton($this->getConfig()->get("Help-Island-Ui-Button-Name"));
       $player->sendForm($form);
       return $form;
      }

      public function islandManageWarning($player){
                  $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
                  $form = $api->createSimpleForm(function (Player $player, int $data = null){
                           $result = $data;
                           if($result === null){
                                    return true;
                           }
                           switch ($result){
                                    //actions when button clicked
                                    case 0:
                                        $this->iswarningtransfer($player);
                                    break;

                                    case 1:
                                        $this->getServer()->dispatchCommand($player, "is disband");
                                    break;
                           }
                  });
       $form->setTitle($this->getConfig()->get("Island-Warning-Ui-Menu-Name"));
       $form->setContent($this->getConfig()->get("Island-Warning-Ui-Content"));
       $form->addButton($this->getConfig()->get("Transfer-Warning-Ui-Button-Name"));
       $form->addButton($this->getConfig()->get("Disband-Warning-Ui-Button-Name"));
       $player->sendForm($form);
       return $form;
         }

   /*
   *
   *
   *
   *
   *
   **/
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

      public function iswarningtransfer($player){ 
      $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
         $form = $api->createCustomForm(function(Player $player, $result){
         if($result === null){
            return;
         }
         if(trim($result[0]) === ""){
            $player->sendActionbarMessage("§cPlease input name");
            return;
         }
         $this->getServer()->getCommandMap()->dispatch($player, "is transfer ".$result[0]);
      });
      $form->setTitle("Transfer island");
      $form->addInput("Input the name");
      $player->sendForm($form);
      }
}

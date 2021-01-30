<?php

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
      $item1 = Item::get($this->getConfig()->get("Item-id-1"), $this->getConfig()->get("Item-meta-1"), 1)->setCustomName($this->getConfig()->get("Create-Island-Item-Name"));
      $item2 = Item::get($this->getConfig()->get("Item-id-2"), $this->getConfig()->get("Item-meta-2"), 1)->setCustomName($this->getConfig()->get("Invite-Manage-Item-Name"));
      $inv->setItem($this->getConfig()->get("Item-Slot-1"), $item1);
      $inv->setItem($this->getConfig()->get("Item-Slot-2"), $item2);
      $menu->send($player);
   }

   public function iscreate(InvMenuTransaction $action) : InvMenuTransactionResult{
      $item = $action->getOut();
      $player = $action->getPlayer();
      $itemClicked = $item;
      if($item->getCustomName() ==  $this->getConfig()->get("Create-Island-Item-Name")){
         $inv = $action->getAction()->getInventory();
         $inv->onClose($player);
         return $action->discard()->then(function(Player $player) : void{
                  $this->IslandCreation2($player);
         });
      }
      if($item->getCustomName() ==  $this->getConfig()->get("Invite-Manage-Item-Name")){
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
      $item3 = Item::get($this->getConfig()->get("Item-id-3"), $this->getConfig()->get("Item-meta-3"), 1)->setCustomName($this->getConfig()->get("Basic-Island-Item-Name"));
      $item4 = Item::get($this->getConfig()->get("Item-id-4"), $this->getConfig()->get("Item-meta-4"), 1)->setCustomName($this->getConfig()->get("Palm-Island-Item-Name"));
      $item5 = Item::get($this->getConfig()->get("Item-id-5"), $this->getConfig()->get("Item-meta-5"), 1)->setCustomName($this->getConfig()->get("Shelly-Island-Item-Name"));
      $item6 = Item::get($this->getConfig()->get("Item-id-6"), $this->getConfig()->get("Item-meta-6"), 1)->setCustomName($this->getConfig()->get("Op-Island-Item-Name"));
      $item7 = Item::get($this->getConfig()->get("Item-id-7"), $this->getConfig()->get("Item-meta-7"), 1)->setCustomName($this->getConfig()->get("Lost-Island-Item-Name"));
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
      if($item->getCustomName() ==  $this->getConfig()->get("Basic-Island-Item-Name")){
         $action->getAction()->getInventory()->onClose($player);
         \pocketmine\Server::getInstance()->dispatchCommand($player, "is create");
         return $action->discard();
      }
      if($item->getCustomName() ==  $this->getConfig()->get("Palm-Island-Item-Name")){
         $action->getAction()->getInventory()->onClose($player);
         \pocketmine\Server::getInstance()->dispatchCommand($player, "is create Palm");
         return $action->discard();
      }
      if($item->getCustomName() ==  $this->getConfig()->get("Shelly-Island-Item-Name")){
         $action->getAction()->getInventory()->onClose($player);
         \pocketmine\Server::getInstance()->dispatchCommand($player, "is create Shelly");
         return $action->discard();
      }
      if($item->getCustomName() ==  $this->getConfig()->get("Op-Island-Item-Name")){
         $action->getAction()->getInventory()->onClose($player);
         \pocketmine\Server::getInstance()->dispatchCommand($player, "is create Op");
         return $action->discard();
      }
      if($item->getCustomName() ==  $this->getConfig()->get("Lost-Island-Item-Name")){
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
      $item8 = Item::get($this->getConfig()->get("Item-id-8"), $this->getConfig()->get("Item-meta-8"), 1)->setCustomName($this->getConfig()->get("Manage-Members-Item-Name"));
      $item9 = Item::get($this->getConfig()->get("Item-id-9"), $this->getConfig()->get("Item-meta-9"), 1)->setCustomName($this->getConfig()->get("Manage-Island-Item-Name"));
      $item10 = Item::get($this->getConfig()->get("Item-id-10"), $this->getConfig()->get("Item-meta-10"), 1)->setCustomName($this->getConfig()->get("Warning-Island-Item-Name"));
      $item23 = Item::get($this->getConfig()->get("Item-id-23"), $this->getConfig()->get("Item-meta-23"), 1)->setCustomName($this->getConfig()->get("Manage-Invite-Item-Name"));
      $inv->setItem($this->getConfig()->get("Item-Slot-8"), $item8);
      $inv->setItem($this->getConfig()->get("Item-Slot-9"), $item9);
      $inv->setItem($this->getConfig()->get("Item-Slot-10"), $item10);
      $inv->setItem($this->getConfig()->get("Item-Slot-23"), $item23);
      $menu->send($player);
   }

   public function ismanage(InvMenuTransaction $action) : InvMenuTransactionResult{
      $item = $action->getOut();
      $player = $action->getPlayer();
      $itemClicked = $item;
      if($item->getCustomName() ==  $this->getConfig()->get("Manage-Members-Item-Name")){
         $inv = $action->getAction()->getInventory();
         $inv->onClose($player);
         return $action->discard()->then(function(Player $player) : void{
                  $this->ismanagemembers($player);
         });
      }
      if($item->getCustomName() ==  $this->getConfig()->get("Manage-Island-Item-Name")){
         $inv = $action->getAction()->getInventory();
         $inv->onClose($player);
         return $action->discard()->then(function(Player $player) : void{
                  $this->ismanageisland($player);
         });
      }
      if($item->getCustomName() ==  $this->getConfig()->get("Manage-Invite-Item-Name")){
         $inv = $action->getAction()->getInventory();
         $inv->onClose($player);
         return $action->discard()->then(function(Player $player) : void{
                  $this->ismanageinvites($player);
         });
      }
      if($item->getCustomName() ==  $this->getConfig()->get("Warning-Island-Item-Name")){
         $inv = $action->getAction()->getInventory();
         $inv->onClose($player);
         return $action->discard()->then(function(Player $player) : void{
                  $this->ismanagewarning($player);
         });
      }
   }

    public function ismanagemembers(Player $player){
      $menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
      $menu->readOnly();
      $menu->setListener(\Closure::fromCallable([$this, "ismanagemember"]));
      $menu->setName($this->getConfig()->get("Management-Members-Menu-Name"));
      $inv = $menu->getInventory();
      $item11 = Item::get($this->getConfig()->get("Item-id-11"), $this->getConfig()->get("Item-meta-11"), 1)->setCustomName($this->getConfig()->get("Cooperate-Members-Item-Name"));
      $item12 = Item::get($this->getConfig()->get("Item-id-12"), $this->getConfig()->get("Item-meta-12"), 1)->setCustomName($this->getConfig()->get("Promote-Members-Item-Name"));
      $item13 = Item::get($this->getConfig()->get("Item-id-13"), $this->getConfig()->get("Item-meta-13"), 1)->setCustomName($this->getConfig()->get("Demote-Members-Item-Name"));
      $item14 = Item::get($this->getConfig()->get("Item-id-14"), $this->getConfig()->get("Item-meta-14"), 1)->setCustomName($this->getConfig()->get("Banish-Members-Item-Name"));
      $item15 = Item::get($this->getConfig()->get("Item-id-15"), $this->getConfig()->get("Item-meta-15"), 1)->setCustomName($this->getConfig()->get("Fire-Members-Item-Name"));
      $item16 = Item::get($this->getConfig()->get("Item-id-16"), $this->getConfig()->get("Item-meta-16"), 1)->setCustomName($this->getConfig()->get("Members-Members-Item-Name"));
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
      if($item->getCustomName() ==  $this->getConfig()->get("Cooperate-Members-Item-Name")){
         $inv = $action->getAction()->getInventory();
         $inv->onClose($player);
         return $action->discard()->then(function(Player $player) : void{
                  $this->ismemberscooperate($player);
         });
      }
      if($item->getCustomName() ==  $this->getConfig()->get("Promote-Members-Item-Name")){
         $inv = $action->getAction()->getInventory();
         $inv->onClose($player);
         return $action->discard()->then(function(Player $player) : void{
                  $this->ismemberspromote($player);
         });
      }
      if($item->getCustomName() ==  $this->getConfig()->get("Demote-Members-Item-Name")){
         $inv = $action->getAction()->getInventory();
         $inv->onClose($player);
         return $action->discard()->then(function(Player $player) : void{
                  $this->ismembersdemote($player);
         });
      }
      if($item->getCustomName() ==  $this->getConfig()->get("Banish-Members-Item-Name")){
         $inv = $action->getAction()->getInventory();
         $inv->onClose($player);
         return $action->discard()->then(function(Player $player) : void{
                  $this->ismembersbanish($player);
         });
      }
      if($item->getCustomName() ==  $this->getConfig()->get("Fire-Members-Item-Name")){
         $inv = $action->getAction()->getInventory();
         $inv->onClose($player);
         return $action->discard()->then(function(Player $player) : void{
                  $this->ismembersfire($player);
         });
      }
      if($item->getCustomName() ==  $this->getConfig()->get("Members-Members-Item-Name")){
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
      $item17 = Item::get($this->getConfig()->get("Item-id-17"), $this->getConfig()->get("Item-meta-17"), 1)->setCustomName($this->getConfig()->get("Join-Island-Item-Name"));
      $item18 = Item::get($this->getConfig()->get("Item-id-18"), $this->getConfig()->get("Item-meta-18"), 1)->setCustomName($this->getConfig()->get("Lock-Island-Item-Name"));
      $item19 = Item::get($this->getConfig()->get("Item-id-19"), $this->getConfig()->get("Item-meta-19"), 1)->setCustomName($this->getConfig()->get("Chat-Island-Item-Name"));
      $item20 = Item::get($this->getConfig()->get("Item-id-20"), $this->getConfig()->get("Item-meta-20"), 1)->setCustomName($this->getConfig()->get("Setspawn-Island-Item-Name"));
      $item21 = Item::get($this->getConfig()->get("Item-id-21"), $this->getConfig()->get("Item-meta-21"), 1)->setCustomName($this->getConfig()->get("Category-Island-Item-Name"));
      $item22 = Item::get($this->getConfig()->get("Item-id-22"), $this->getConfig()->get("Item-meta-22"), 1)->setCustomName($this->getConfig()->get("Blocks-Island-Item-Name"));
      $item30 = Item::get($this->getConfig()->get("Item-id-30"), $this->getConfig()->get("Item-meta-30"), 1)->setCustomName($this->getConfig()->get("Visit-Island-Item-Name"));
      $item31 = Item::get($this->getConfig()->get("Item-id-31"), $this->getConfig()->get("Item-meta-31"), 1)->setCustomName($this->getConfig()->get("Help-Island-Item-Name"));
      $inv->setItem($this->getConfig()->get("Item-Slot-17"), $item17);
      $inv->setItem($this->getConfig()->get("Item-Slot-18"), $item18);
      $inv->setItem($this->getConfig()->get("Item-Slot-19"), $item19);
      $inv->setItem($this->getConfig()->get("Item-Slot-20"), $item20);
      $inv->setItem($this->getConfig()->get("Item-Slot-21"), $item21);
      $inv->setItem($this->getConfig()->get("Item-Slot-22"), $item22);
      $inv->setItem($this->getConfig()->get("Item-Slot-30"), $item30);
      $inv->setItem($this->getConfig()->get("Item-Slot-31"), $item31);
      $menu->send($player);
   }

   public function ismanageislan(InvMenuTransaction $action) : InvMenuTransactionResult{
      $item = $action->getOut();
      $player = $action->getPlayer();
      $itemClicked = $item;
      if($item->getCustomName() ==  $this->getConfig()->get("Join-Island-Item-Name")){
         $action->getAction()->getInventory()->onClose($player);
         \pocketmine\Server::getInstance()->dispatchCommand($player, "is join");
         return $action->discard();
      }
      if($item->getCustomName() ==  $this->getConfig()->get("Lock-Island-Item-Name")){
         $action->getAction()->getInventory()->onClose($player);
         \pocketmine\Server::getInstance()->dispatchCommand($player, "is lock");
         return $action->discard();
      }
      if($item->getCustomName() ==  $this->getConfig()->get("Chat-Island-Item-Name")){
         $action->getAction()->getInventory()->onClose($player);
         \pocketmine\Server::getInstance()->dispatchCommand($player, "is chat");
         return $action->discard();
      }
      if($item->getCustomName() ==  $this->getConfig()->get("Setspawn-Island-Item-Name")){
         $action->getAction()->getInventory()->onClose($player);
         \pocketmine\Server::getInstance()->dispatchCommand($player, "is setspawn");
         return $action->discard();
      }
      if($item->getCustomName() ==  $this->getConfig()->get("Category-Island-Item-Name")){
         $action->getAction()->getInventory()->onClose($player);
         \pocketmine\Server::getInstance()->dispatchCommand($player, "is category");
         return $action->discard();
      }
      if($item->getCustomName() ==  $this->getConfig()->get("Blocks-Island-Item-Name")){
         $action->getAction()->getInventory()->onClose($player);
         \pocketmine\Server::getInstance()->dispatchCommand($player, "is blocks");
         return $action->discard();
      }
      if($item->getCustomName() ==  $this->getConfig()->get("Visit-Island-Item-Name")){
         $inv = $action->getAction()->getInventory();
         $inv->onClose($player);
         return $action->discard()->then(function(Player $player) : void{
                  $this->isvisit($player);
         });
      }
      if($item->getCustomName() ==  $this->getConfig()->get("Help-Island-Item-Name")){
         $action->getAction()->getInventory()->onClose($player);
         \pocketmine\Server::getInstance()->dispatchCommand($player, "is help");
         return $action->discard();
      }
   }

   public function ismanageinvites(Player $player){
      $menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
      $menu->readOnly();
      $menu->setListener(\Closure::fromCallable([$this, "ismanageinvite"]));
      $menu->setName($this->getConfig()->get("Management-Invite-Menu-Name"));
      $inv = $menu->getInventory();
      $item24 = Item::get($this->getConfig()->get("Item-id-24"), $this->getConfig()->get("Item-meta-24"), 1)->setCustomName($this->getConfig()->get("Accept-Invite-Item-Name"));
      $item25 = Item::get($this->getConfig()->get("Item-id-25"), $this->getConfig()->get("Item-meta-25"), 1)->setCustomName($this->getConfig()->get("Deny-Invite-Item-Name"));
      $item26 = Item::get($this->getConfig()->get("Item-id-26"), $this->getConfig()->get("Item-meta-26"), 1)->setCustomName($this->getConfig()->get("Invite-Invite-Item-Name"));
      $inv->setItem($this->getConfig()->get("Item-Slot-24"), $item24);
      $inv->setItem($this->getConfig()->get("Item-Slot-25"), $item25);
      $inv->setItem($this->getConfig()->get("Item-Slot-26"), $item26);
      $menu->send($player);
   }

   public function ismanageinvite(InvMenuTransaction $action) : InvMenuTransactionResult{
      $item = $action->getOut();
      $player = $action->getPlayer();
      $itemClicked = $item;
      if($item->getCustomName() ==  $this->getConfig()->get("Accept-Invite-Item-Name")){
         $inv = $action->getAction()->getInventory();
         $inv->onClose($player);
         return $action->discard()->then(function(Player $player) : void{
                  $this->isinviteaccept($player);
         });
      }
      if($item->getCustomName() ==  $this->getConfig()->get("Deny-Invite-Item-Name")){
         $inv = $action->getAction()->getInventory();
         $inv->onClose($player);
         return $action->discard()->then(function(Player $player) : void{
                  $this->isinvitedeny($player);
         });
      }
      if($item->getCustomName() ==  $this->getConfig()->get("Invite-Invite-Item-Name")){
         $inv = $action->getAction()->getInventory();
         $inv->onClose($player);
         return $action->discard()->then(function(Player $player) : void{
                  $this->isinviteinvite($player);
         });
      }
   }

   public function ismanagewarning(Player $player){
      $menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
      $menu->readOnly();
      $menu->setListener(\Closure::fromCallable([$this, "ismanagewarnin"]));
      $menu->setName($this->getConfig()->get("Management-Warning-Menu-Name"));
      $inv = $menu->getInventory();
      $item27 = Item::get($this->getConfig()->get("Item-id-27"), $this->getConfig()->get("Item-meta-27"), 1)->setCustomName($this->getConfig()->get("Transfer-Warning-Item-Name"));
      $item28 = Item::get($this->getConfig()->get("Item-id-28"), $this->getConfig()->get("Item-meta-28"), 1)->setCustomName($this->getConfig()->get("Disband-Warning-Item-Name"));
      $inv->setItem($this->getConfig()->get("Item-Slot-27"), $item27);
      $inv->setItem($this->getConfig()->get("Item-Slot-28"), $item28);
      $menu->send($player);
   }

   public function ismanagewarnin(InvMenuTransaction $action) : InvMenuTransactionResult{
      $item = $action->getOut();
      $player = $action->getPlayer();
      $itemClicked = $item;
      if($item->getCustomName() ==  $this->getConfig()->get("Transfer-Warning-Item-Name")){
         $inv = $action->getAction()->getInventory();
         $inv->onClose($player);
         return $action->discard()->then(function(Player $player) : void{
                  $this->iswarningtransfer($player);
         });
      }
      if($item->getCustomName() ==  $this->getConfig()->get("Disband-Warning-Item-Name")){
         $action->getAction()->getInventory()->onClose($player);
         \pocketmine\Server::getInstance()->dispatchCommand($player, "is disband");
         return $action->discard();
      }
   }

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

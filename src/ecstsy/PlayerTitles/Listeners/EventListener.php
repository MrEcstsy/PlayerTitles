<?php

namespace ecstsy\PlayerTitles\Listeners;

use ecstsy\PlayerTitles\Player\PlayerManager;
use ecstsy\PlayerTitles\Utils\Utils;
use IvanCraft623\RankSystem\RankSystem;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\utils\TextFormat as C;

class EventListener implements Listener {

    public function onLogin(PlayerLoginEvent $event) {
        $player = $event->getPlayer();
        
        if (PlayerManager::getInstance()->getSession($player) === null) {
            PlayerManager::getInstance()->createSession($player);
        }
    }

    public function onJoin(PlayerJoinEvent $event) {
        $player = $event->getPlayer();
        
        PlayerManager::getInstance()->getSession($player)->setConnected(true);
    }

    public function onQuit(PlayerQuitEvent $event) {
        $player = $event->getPlayer();
        PlayerManager::getInstance()->getSession($player)->setConnected(false);
    }

    public function onUsePlayerTitle(PlayerItemUseEvent $event) {
        $player = $event->getPlayer();
        $item = $event->getItem();
        $namedTag = $item->getNamedTag();
        $config = Utils::getConfiguration("config.yml")->getAll();
    
        if (($tag = $namedTag->getTag("player_title")) === null) {
            return; 
        }
    
        $title = $tag->getValue();
        
        if (!isset($config["titles"][$title])) {
            $player->sendMessage(C::colorize("&cTitle '$title' does not exist."));
            return;
        }
    
        $session = RankSystem::getInstance()->getSessionManager()->get($player);
    
        $permission = "playertitles.title." . $title;
        if ($player->hasPermission($permission)) {
            $player->sendMessage(C::colorize("&cYou already have that title!"));
            return;
        }
    
        $titleConfig = $config["titles"][$title];
        $titleType = $config["settings"]["item"]["title-type"];
        
        $titleValue = ($titleType === "display" && isset($titleConfig["display"])) ? 
                       $titleConfig["display"] : ucfirst($title);
    
        $item->pop();
        $player->getInventory()->setItemInHand($item);
    
        $session->setPermission($permission);
    
        $claimMessage = str_replace("{title}", $titleValue, $config['settings']['claim-message']);
        $player->sendMessage(C::colorize($claimMessage));
    
        Utils::playSound($player, $config['settings']['claim-sound']);
    }
}

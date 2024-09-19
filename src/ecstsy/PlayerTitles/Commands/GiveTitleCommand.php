<?php

namespace ecstsy\PlayerTitles\Commands;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use ecstsy\PlayerTitles\Utils\Utils;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;

class GiveTitleCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());

        $this->registerArgument(0, new RawStringArgument("name", false));
        $this->registerArgument(1, new RawStringArgument("title", false));
        $this->registerArgument(2, new IntegerArgument("amount", true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        $player = isset($args["name"]) ? Utils::getPlayerByPrefix($args["name"]) : null;
        $title = isset($args["title"]) ? $args["title"] : null;
        $amount = isset($args["amount"]) ? (int)$args["amount"] : 1; 

        if ($player === null) {
            $sender->sendMessage(C::colorize("&cPlayer not found or is offline."));
            return;
        }

        if ($title === null) {
            $sender->sendMessage(C::colorize("&cTitle cannot be null. Please provide a valid title."));
            return;
        }

        try {
            $item = Utils::createPlayerTitle($title, $amount);
            $player->getInventory()->addItem($item);
            $sender->sendMessage(C::colorize("&aSuccessfully gave $amount of title '{$title}' to {$player->getName()}."));
        } catch (\Exception $e) {
            $sender->sendMessage(C::colorize("&cError: " . $e->getMessage()));
        }
    }

    public function getPermission(): string
    {
        return "playertitles.command.give";
    }
}
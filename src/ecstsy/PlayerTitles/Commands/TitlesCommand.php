<?php

namespace ecstsy\PlayerTitles\Commands;

use CortexPE\Commando\BaseCommand;
use ecstsy\PlayerTitles\Utils\TitlesForm;
use ecstsy\PlayerTitles\Utils\Utils;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;

class TitlesCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::colorize("&cThis command can only be used in-game."));
            return;
        }

        $sender->sendForm(TitlesForm::getTitleForm($sender));
    }
    
    public function getPermission(): string {
        return "playertitles.command";
    }
}
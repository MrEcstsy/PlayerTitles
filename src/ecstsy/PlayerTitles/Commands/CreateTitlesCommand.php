<?php

namespace ecstsy\PlayerTitles\Commands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use ecstsy\PlayerTitles\Utils\Utils;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as C;

class CreateTitlesCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());

        $this->registerArgument(0, new RawStringArgument("identifier", false));
        $this->registerArgument(1, new RawStringArgument("display", false));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $config = Utils::getConfiguration("config.yml");
        $identifier = isset($args["identifier"]) ? $args["identifier"] : null;
        $display = isset($args["display"]) ? $args["display"] : null;

        if ($identifier !== null && $display !== null) {
            $titles = $config->get("titles", []);
            if (isset($titles[$identifier])) {
                $sender->sendMessage(C::colorize("&r&c&l(!) &r&cTitle identifier '&4$identifier&c' already exists."));
                return;
            }

            $titles[$identifier] = [
                'display' => $display
            ];
            $config->set("titles", $titles);

            $config->save();

            $sender->sendMessage(C::colorize("&r&a&l(!) &r&aTitle '$identifier' with display '$display&r&a' has been added successfully."));
        } else {
            $sender->sendMessage("Usage: /createTitle <identifier> <display>");

        } 
    }

    public function getPermission(): string {
        return "playertitles.command.create";
    }
}
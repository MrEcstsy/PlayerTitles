<?php

namespace ecstsy\PlayerTitles\Utils;

use ecstsy\PlayerTitles\Loader;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as C;
use RuntimeException;

class Utils {

    private static array $configCache = [];

    /**
     * @throws RuntimeException if the configuration file is not found or if the format is unsupported.
     */
    public static function getConfiguration(string $fileName): Config {
        $pluginFolder = Loader::getInstance()->getDataFolder();
        $filePath = $pluginFolder . $fileName;

        if (isset(self::$configCache[$filePath])) {
            return self::$configCache[$filePath];
        }

        if (!file_exists($filePath)) {
            throw new RuntimeException("Configuration file '$filePath' not found.");
        }
        
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);

        switch ($extension) {
            case 'yml':
            case 'yaml':
                $config = new Config($filePath, Config::YAML);
                break;
    
            case 'json':
                $config = new Config($filePath, Config::JSON);
                break;
    
            default:
                throw new RuntimeException("Unsupported configuration file format for '$filePath'.");
        }

        self::$configCache[$filePath] = $config;
        return $config;
    }

    public static function getPermissionLockedStatus(Player $player, string $permission) : string {
        if ($player->hasPermission($permission)) {
            $text = C::RESET . C::GREEN . C::BOLD . "UNLOCKED";
        } else {
            $text = C::RESET . C::RED . C::BOLD . "LOCKED";
        }

        return $text;
    }

    /**
     * Returns an online player whose name begins with or equals the given string (case insensitive).
     * The closest match will be returned, or null if there are no online matches.
     *
     * @param string $name The prefix or name to match.
     * @return Player|null The matched player or null if no match is found.
     */
    public static function getPlayerByPrefix(string $name): ?Player {
        $found = null;
        $name = strtolower($name);
        $delta = PHP_INT_MAX;

        /** @var Player[] $onlinePlayers */
        $onlinePlayers = Server::getInstance()->getOnlinePlayers();

        foreach ($onlinePlayers as $player) {
            if (stripos($player->getName(), $name) === 0) {
                $curDelta = strlen($player->getName()) - strlen($name);

                if ($curDelta < $delta) {
                    $found = $player;
                    $delta = $curDelta;
                }

                if ($curDelta === 0) {
                    break;
                }
            }
        }

        return $found;
    }

}

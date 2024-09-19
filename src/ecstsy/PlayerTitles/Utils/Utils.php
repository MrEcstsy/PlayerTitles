<?php

namespace ecstsy\PlayerTitles\Utils;

use ecstsy\PlayerTitles\Loader;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
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

    /**
     * @param Entity $player
     * @param string $sound
     * @param int $volume
     * @param int $pitch
     * @param int $radius
     */
    public static function playSound(Entity $player, string $sound, $volume = 1, $pitch = 1, int $radius = 5): void
    {
        foreach ($player->getWorld()->getNearbyEntities($player->getBoundingBox()->expandedCopy($radius, $radius, $radius)) as $p) {
            if ($p instanceof Player) {
                if ($p->isOnline()) {
                    $spk = new PlaySoundPacket();
                    $spk->soundName = $sound;
                    $spk->x = $p->getLocation()->getX();
                    $spk->y = $p->getLocation()->getY();
                    $spk->z = $p->getLocation()->getZ();
                    $spk->volume = $volume;
                    $spk->pitch = $pitch;
                    $p->getNetworkSession()->sendDataPacket($spk);
                }
            }
        }
    }
     
    public static function createPlayerTitle(string $title, int $amount = 1): Item {
        $config = self::getConfiguration("config.yml")->getAll();
    
        if (!isset($config["titles"][$title])) {
            throw new \InvalidArgumentException("Title '$title' does not exist.");
        }
    
        $titleSettings = $config["titles"][$title];
        $itemType = $config["settings"]['item']['type'] ?? "minecraft:paper"; // Default to paper if type not set
        $itemName = $config["settings"]['item']['name'] ?? "&r&l&b* &r&bPlayer Title &r&l*&r";
        $itemLore = $config["settings"]['item']['lore'] ?? [
            "&r&7Unlocks a special player title.",
            "&r&7Redeem to gain {title}."
        ];
    
        $item = StringToItemParser::getInstance()->parse($itemType)->setCount($amount);
        if ($item === null) {
            throw new \InvalidArgumentException("Invalid item type '$itemType'.");
        }
    
        $titleType = $config["settings"]["item"]["title-type"] ?? "display";
        $titleValue = ($titleType === "identifier")
            ? ucfirst($title)
            : ($titleSettings['display'] ?? ucfirst($title)); 
    
        $finalName = str_replace("{title}", $titleValue, $itemName);
        $finalLore = array_map(fn($line) => str_replace("{title}", $titleValue, $line), $itemLore);
    
        $item->setCustomName(C::colorize($finalName));
        $item->setLore(array_map([C::class, "colorize"], $finalLore));
    
        $item->getNamedTag()->setString("player_title", $title);
        return $item;
    }
}

<?php

namespace ecstsy\PlayerTitles;

use ecstsy\PlayerTitles\Commands\CreateTitlesCommand;
use ecstsy\PlayerTitles\Commands\TitlesCommand;
use ecstsy\PlayerTitles\Listeners\EventListener;
use ecstsy\PlayerTitles\Player\PlayerManager;
use ecstsy\PlayerTitles\Utils\Queries;
use ecstsy\PlayerTitles\Utils\Utils;
use IvanCraft623\RankSystem\RankSystem;
use IvanCraft623\RankSystem\session\Session;
use IvanCraft623\RankSystem\tag\Tag;
use JackMD\ConfigUpdater\ConfigUpdater;
use pocketmine\command\defaults\VanillaCommand;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;

class Loader extends PluginBase {

    use SingletonTrait;

    public int $configVer = 1;
    
    public static DataConnector $connector;

    public static PlayerManager $manager;
    
    public function onLoad(): void {
        self::setInstance($this);
    }   

    public function onEnable(): void {
        ConfigUpdater::checkUpdate($this,  $this->getConfig(), "version", $this->configVer);
        $config = Utils::getConfiguration("config.yml");

        self::$connector = libasynql::create($this, ["type" => "sqlite", "sqlite" => ["file" => "sqlite.sql"], "worker-limit" => 2], ["sqlite" => "sqlite.sql"]);
        self::$connector->executeGeneric(Queries::PLAYERS_INIT);
        self::$connector->waitAll();

        self::$manager = new PlayerManager($this);

        if ($this->getServer()->getPluginManager()->getPlugin("RankSystem") !== null) {
            $rankSystem = RankSystem::getInstance();
            $tagManager = $rankSystem->getTagManager();

            $tagManager->registerTag(new Tag("title", static function(Session $session): string {
                $playerSession = Loader::getPlayerMAnager()->getSession($session->getPlayer());
                return $playerSession->getTitle();
            }));
        }

        $listeners = [
            new EventListener()
        ];

        foreach ($listeners as $listener) {
            $this->getServer()->getPluginManager()->registerEvents($listener, $this);
        }

        $this->unregisterVanillaCommand($config->getNested("commands.titles.name"));

        $this->getServer()->getCommandMap()->registerAll("PlayerTitles", [
            new TitlesCommand($this, $config->getNested("commands.titles.name"), $config->getNested("commands.titles.description"), $config->getNested("commands.titles.aliases")),
            new CreateTitlesCommand($this, "createtitle", "Create a new title", ["ctitle"])
        ]);
        
    }

    /**
     * Unregisters a vanilla command if it conflicts with the plugin command.
     *
     * @param string $commandName The name of the command to unregister if it's a vanilla command.
     */
    private function unregisterVanillaCommand(string $commandName): void {
        $commandMap = $this->getServer()->getCommandMap();
        $command = $commandMap->getCommand($commandName);

        if ($command instanceof VanillaCommand) {
            $command->unregister($commandMap);
            $this->getLogger()->info("Unregistered vanilla command: {$commandName}");
        }
    }

    public static function getDatabase(): DataConnector {
        return self::$connector;
    }

    public static function getPlayerManager(): PlayerManager {
        return self::$manager;
    }
}
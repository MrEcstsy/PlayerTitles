<?php

declare(strict_types=1);

namespace ecstsy\PlayerTitles\Player;

use ecstsy\PlayerTitles\Loader;
use ecstsy\PlayerTitles\Utils\Queries;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class PlayerManager
{
    use SingletonTrait;

    /** @var PTPlayer[] */
    private array $sessions; // array to fetch player data

    public function __construct(
        public Loader $plugin
    ){
        self::setInstance($this);

        $this->loadSessions();
    }

    /**
     * Store all player data in $sessions property
     *
     * @return void
     */
    private function loadSessions(): void
    {
        Loader::getDatabase()->executeSelect(Queries::PLAYERS_SELECT, [], function (array $rows): void {
            foreach ($rows as $row) {
                $this->sessions[$row["uuid"]] = new PTPlayer(
                    Uuid::fromString($row["uuid"]),
                    $row["username"],
                    $row["title"]
                );
            }
        });
    }

    /**
     * Create a session
     *
     * @param Player $player
     * @return PTPlayer
     * @throws \JsonException
     */
    public function createSession(Player $player): PTPlayer
    {
        $args = [
            "uuid" => $player->getUniqueId()->toString(),
            "username" => $player->getName(),
            "title" => "",
        ];

        Loader::getDatabase()->executeInsert(Queries::PLAYERS_CREATE, $args);

        $this->sessions[$player->getUniqueId()->toString()] = new PTPlayer(
            $player->getUniqueId(),
            $args["username"],
            $args["title"]
        );
        return $this->sessions[$player->getUniqueId()->toString()];
    }

    /**
     * Get session by player object
     *
     * @param Player $player
     * @return PTPlayer|null
     */
    public function getSession(Player $player) : ?PTPlayer
    {
        return $this->getSessionByUuid($player->getUniqueId());
    }

    /**
     * Get session by player name
     *
     * @param string $name
     * @return PTPlayer|null
     */
    public function getSessionByName(string $name) : ?PTPlayer
    {
        foreach ($this->sessions as $session) {
            if (strtolower($session->getUsername()) === strtolower($name)) {
                return $session;
            }
        }
        return null;
    }

    /**
     * Get session by UuidInterface
     *
     * @param UuidInterface $uuid
     * @return PTPlayer|null
     */
    public function getSessionByUuid(UuidInterface $uuid) : ?PTPlayer
    {
        return $this->sessions[$uuid->toString()] ?? null;
    }

    public function destroySession(PTPlayer $session) : void
    {
        Loader::getDatabase()->executeChange(Queries::PLAYERS_DELETE, ["uuid", $session->getUuid()->toString()]);

        # Remove session from the array
        unset($this->sessions[$session->getUuid()->toString()]);
    }

    public function getSessions() : array
    {
        return $this->sessions;
    }

}
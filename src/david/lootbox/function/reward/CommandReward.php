<?php

declare(strict_types=1);

namespace david\lootbox\function\reward;

use pocketmine\console\ConsoleCommandSender;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\Server;

class CommandReward extends Reward {
    /**
     * CommandReward constructor.
     *
     * @param string $name
     * @param Item $item
     * @param string $command
     * @param int $chance
     */
    public function __construct(string $name, Item $item, protected string $command, int $chance) {
        $server = Server::getInstance();
        $callable = function (Player $player) use ($server) {
            $player->getServer()->dispatchCommand(new ConsoleCommandSender($server, $server->getLanguage()), str_replace("{player}", $player->getName(), $this->command));
        };
        parent::__construct($name, $item, $callable, $chance);
    }

    /**
     * @return string
     */
    public function getCommand(): string {
        return $this->command;
    }
}
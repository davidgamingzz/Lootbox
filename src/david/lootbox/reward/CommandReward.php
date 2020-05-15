<?php

declare(strict_types = 1);

namespace david\lootbox\reward;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\item\Item;
use pocketmine\Player;

class CommandReward extends Reward {

    /** @var string */
    protected $command;

    /**
     * CommandReward constructor.
     *
     * @param string $name
     * @param Item $item
     * @param string $command
     * @param int $chance
     */
    public function __construct(string $name, Item $item, string $command, int $chance) {
        $this->command = $command;
        $callable = function(Player $player) {
            $player->getServer()->dispatchCommand(new ConsoleCommandSender(), str_replace("{player}", $player->getName(), $this->command));
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
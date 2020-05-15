<?php

declare(strict_types=1);

namespace david\lootbox\command;

use david\lootbox\Loader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class GiveLootBoxCommand extends Command {

    /**
     * GiveLootBoxCommand constructor.
     */
    public function __construct() {
        parent::__construct("givelootbox", "Give lootbox to a player.", "/givelootbox <player> <identifier> [amount = 1]");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if($sender instanceof ConsoleCommandSender or $sender->isOp()) {
            if(!isset($args[2])) {
                $sender->sendMessage(TextFormat::YELLOW . $this->getUsage());
                return;
            }
            $player = Loader::getInstance()->getServer()->getPlayer($args[0]);
            if(!$player instanceof Player) {
                $sender->sendMessage(TextFormat::DARK_RED . TextFormat::BOLD . "Invalid player!");
                return;
            }
            $lootbox = Loader::getInstance()->getLootboxManager()->getLootbox($args[1]);
            if($lootbox === null) {
                $sender->sendMessage(TextFormat::DARK_RED . TextFormat::BOLD . "Invalid lootbox!");
                return;
            }
            $amount = max(1, is_numeric($args[2]) ? (int)$args[2] : 1);
            $item = $lootbox->getItem();
            $item->setCount($amount);
            $player->getInventory()->addItem($item);
            return;
        }
        $sender->sendMessage(TextFormat::DARK_RED . TextFormat::BOLD . "Insufficient permission!");
        return;
    }
}
<?php

declare(strict_types=1);

namespace david\lootbox\command;

use david\lootbox\Loader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class GiveLootBoxCommand extends Command {
    /**
     * GiveLootBoxCommand constructor.
     */
    public function __construct() {
        parent::__construct("givelootbox", "Give lootbox to a player.", "/givelootbox <player> <identifier> [amount = 1]");
        $this->setPermission("lootbox.give");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        $server = Server::getInstance();
        if($sender instanceof ConsoleCommandSender or $server->isOp($sender->getName())) {
            if(!isset($args[2])) {
                $sender->sendMessage(TextFormat::YELLOW . $this->getUsage());
                return;
            }

            $player = Loader::getInstance()->getServer()->getPlayerExact($args[0]);
            if(!$player instanceof Player) {
                $sender->sendMessage(TextFormat::DARK_RED . TextFormat::BOLD . "Invalid player!");
                return;
            }

            $lootbox = Loader::getInstance()->getLootboxManager()->getLootbox($args[1]);
            if($lootbox === null) {
                $sender->sendMessage(TextFormat::DARK_RED . TextFormat::BOLD . "Invalid lootbox!\n");

                $identifiers = [];
                $lootboxes = Loader::getInstance()->getLootboxManager()->getLootboxes();
                foreach ($lootboxes as $lootbox) {
                    $identifiers[] = $lootbox->getIdentifier();
                }
                $sender->sendMessage(TextFormat::DARK_RED . TextFormat::BOLD . "Available lootboxes: " . implode(", ", $identifiers));
                return;
            }

            $amount = max(1, is_numeric($args[2]) ? (int)$args[2] : 1);
            $item = $lootbox->getItem();
            $item->setCount($amount);
            $player->getInventory()->addItem($item);
            return;
        }
        $sender->sendMessage(TextFormat::DARK_RED . TextFormat::BOLD . "Insufficient permission!");
    }
}
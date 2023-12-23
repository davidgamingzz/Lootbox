<?php

namespace david\lootbox;

use muqsit\invmenu\InvMenuHandler;
use david\lootbox\command\GiveLootBoxCommand;
use david\lootbox\function\types\LootboxManager;
use david\lootbox\utils\Utils;
use pocketmine\plugin\PluginBase;

class Loader extends PluginBase {
    /** @var self */
    private static self $instance;

    /** @var LootboxManager */
    private LootboxManager $lootboxManager;

    /** @return self */
    public static function getInstance(): self {
        return self::$instance;
    }

    public function onLoad(): void {
        self::$instance = $this;
        Utils::loadAllResources($this);
    }

    public function onEnable(): void {
        if (!InvMenuHandler::isRegistered()) {
            InvMenuHandler::register($this);
        }

        $this->lootboxManager = new LootboxManager($this);
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->getServer()->getCommandMap()->register("lootbox", new GiveLootBoxCommand($this));
    }

    /** @return LootboxManager */
    public function getLootboxManager(): LootboxManager {
        return $this->lootboxManager;
    }
}

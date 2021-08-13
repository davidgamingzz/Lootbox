<?php

namespace david\lootbox;

use david\lootbox\command\GiveLootBoxCommand;
use david\lootbox\types\LootboxManager;
use muqsit\invmenu\InvMenuHandler;
use pocketmine\plugin\PluginBase;

class Loader extends PluginBase {

    /** @var self */
    private static $instance;

    /** @var LootboxManager */
    private $lootboxManager;

    public function onLoad() {
        if(!is_dir($this->getDataFolder())) {
            mkdir($this->getDataFolder());
        }
        if(!is_dir($this->getDataFolder() . "lootboxes")) {
            mkdir($this->getDataFolder() . "lootboxes");
        }
        foreach(scandir($this->getDataFolder() . "lootboxes") as $file) {
            if($file === "." || $file === "..") continue;
            if(pathinfo($this->getDataFolder() . "lootboxes" . $file, PATHINFO_EXTENSION) !== "yml") continue;
            $this->saveResource("lootboxes" . DIRECTORY_SEPARATOR . $file);
        }
        self::$instance = $this;
    }

    public function onEnable() {
        $this->lootboxManager = new LootboxManager($this);
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->getServer()->getCommandMap()->register("givelootbox", new GiveLootBoxCommand());
        if(!InvMenuHandler::isRegistered()){
            InvMenuHandler::register($this);
        }
    }

    /**
     * @return self
     */
    public static function getInstance(): self {
        return self::$instance;
    }

    /**
     * @return LootboxManager
     */
    public function getLootboxManager(): LootboxManager {
        return $this->lootboxManager;
    }
}

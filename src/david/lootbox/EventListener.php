<?php

declare(strict_types=1);

namespace david\lootbox;

use david\lootbox\animations\Animation;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;

class EventListener implements Listener {

    /** @var Loader */
    private $plugin;

    /**
     * EventListener constructor.
     *
     * @param Loader $plugin
     */
    public function __construct(Loader $plugin) {
        $this->plugin = $plugin;
    }

    /**
     * @priority HIGHEST
     * @param PlayerInteractEvent $event
     */
    public function onPlayerInteract(PlayerInteractEvent $event): void {
        $item = $event->getItem();
        $player = $event->getPlayer();
        if(!$player instanceof Player) {
            return;
        }
        $inventory = $player->getInventory();
        $tag = $item->getNamedTagEntry("Lootbox");
        if($tag === null) {
            return;
        }
        if($tag instanceof CompoundTag) {
            if($tag->hasTag("Identifier", StringTag::class)) {
                $identifier = $tag->getString("Identifier");
                $lootbox = $this->plugin->getLootboxManager()->getLootbox($identifier);
                $inventory->setItemInHand($item->setCount($item->getCount() - 1));
                Animation::startAnimation($player, $lootbox);
                $event->setCancelled();
            }
        }
    }
}
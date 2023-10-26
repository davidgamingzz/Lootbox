<?php

declare(strict_types=1);

namespace david\lootbox;

use david\lootbox\animations\Animation;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;

class EventListener implements Listener {
    /** @var Loader */
    private Loader $plugin;

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
    public function onPlayerInteract(PlayerInteractEvent $event): void{
        $item = $event->getItem();
        $player = $event->getPlayer();
        $inventory = $player->getInventory();

        $tag = $item->getNamedTag();
        if ($tag->getTag("Identifier") === null) return;

        $identifier = $tag->getString("Identifier");
        $lootbox = $this->plugin->getLootboxManager()->getLootbox($identifier);
        $inventory->setItemInHand($item->setCount($item->getCount() - 1));
        Animation::startAnimation($player, $lootbox);
        $event->cancel();
    }
}
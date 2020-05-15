<?php

declare(strict_types = 1);

namespace david\lootbox\reward;

use pocketmine\item\Item;
use pocketmine\Player;

class ItemReward extends Reward {

    /** @var Item */
    protected $givenItem;

    /**
     * ItemReward constructor.
     *
     * @param string $name
     * @param Item $item
     * @param Item $givenItem
     * @param int $chance
     */
    public function __construct(string $name, Item $item, Item $givenItem, int $chance) {
        $this->givenItem = $givenItem;
        $callable = function(Player $player) {
            $player->getInventory()->addItem($this->givenItem);
        };
        parent::__construct($name, $item, $callable, $chance);
    }

    /**
     * @return Item
     */
    public function getGivenItem(): Item {
        return $this->givenItem;
    }
}
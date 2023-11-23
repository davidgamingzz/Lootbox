<?php

declare(strict_types=1);

namespace david\lootbox\function\reward;

use pocketmine\item\Item;
use pocketmine\player\Player;

class ItemReward extends Reward {
    /**
     * ItemReward constructor.
     *
     * @param string $name
     * @param Item $item
     * @param Item $givenItem
     * @param int $chance
     */
    public function __construct(string $name, Item $item, protected Item $givenItem, int $chance) {
        $callable = function (Player $player) {
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
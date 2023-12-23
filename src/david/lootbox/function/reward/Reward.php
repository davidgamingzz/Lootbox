<?php

declare(strict_types=1);

namespace david\lootbox\function\reward;

use pocketmine\item\Item;

class Reward {
    /** @var callable */
    protected $callback;

    /**
     * Reward constructor.
     *
     * @param string $name
     * @param Item $item
     * @param \Closure $callable
     * @param int $chance
     */
    public function __construct(protected string $name, protected Item $item, callable $callable, protected int $chance) {
        $this->callback = $callable;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return Item
     */
    public function getItem(): Item {
        return $this->item;
    }

    /**
     * @return callable
     */
    public function getCallback(): callable {
        return $this->callback;
    }

    /**
     * @return int
     */
    public function getChance(): int {
        return $this->chance;
    }
}
<?php

declare(strict_types = 1);

namespace david\lootbox\types;

use david\lootbox\reward\Reward;
use pocketmine\item\Item;
use pocketmine\utils\TextFormat;

class Lootbox {
    /** @var string */
    private string $name;

    /** @var string */
    private string $displayName;

    /** @var string */
    private string $identifier;

    /** @var Item */
    private Item $item;

    /** @var string */
    private string $animationType;

    /** @var Reward[] */
    private array $rewards = [];

    /**
     * Lootbox constructor.
     *
     * @param string $name
     * @param string $displayName
     * @param string $identifier
     * @param Item $item
     * @param string $animationType
     * @param array $rewards
     */
    public function __construct(string $name, string $displayName, string $identifier, Item $item, string $animationType, array $rewards) {
        $this->name = $name;
        $this->displayName = $displayName;
        $this->identifier = $identifier;
        $this->item = $item;
        $this->animationType = $animationType;
        $this->rewards = $rewards;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDisplayName(): string {
        return TextFormat::colorize($this->displayName);
    }

    /**
     * @return string
     */
    public function getIdentifier(): string {
        return $this->identifier;
    }

    /**
     * @return Item
     */
    public function getItem(): Item {
        return $this->item;
    }

    /**
     * @return string
     */
    public function getAnimationType(): string {
        return $this->animationType;
    }

    /**
     * @return Reward[]
     */
    public function getRewards(): array {
        return $this->rewards;
    }
}
<?php

declare(strict_types=1);

namespace david\lootbox\function\types;

use david\lootbox\function\reward\Reward;
use pocketmine\item\Item;
use pocketmine\utils\TextFormat;

class Lootbox {
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
    public function __construct(private string $name, private string $displayName, private string $identifier, private Item $item, private string $animationType, private array $rewards = []) { }

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
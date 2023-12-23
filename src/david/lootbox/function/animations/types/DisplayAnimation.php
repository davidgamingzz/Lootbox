<?php

declare(strict_types=1);

namespace david\lootbox\function\animations\types;

use david\lootbox\function\animations\Animation;
use pocketmine\entity\object\ItemEntity;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;

class DisplayAnimation extends Animation {
    /** @var ItemEntity */
    private ItemEntity $itemEntity;

    /**
     * DisplayAnimation constructor.
     *
     * @param Player $owner
     * @param array $rewards
     */
    public function __construct(Player $owner, array $rewards) {
        parent::__construct($owner, $rewards);
    }

    /**
     * @param Task $task
     */
    public function tick(Task $task): void {
        parent::tick($task);
        if ($this->ticks === 1) {
            $reward = $this->getReward();
            $callable = $reward->getCallback();
            $callable($this->owner);
        }
        if ($this->ticks === 3) {
            $reward = $this->getReward();
            $item = $reward->getItem();
            $directionVector = $this->owner->getDirectionVector();
            $this->itemEntity = $this->owner->getWorld()->dropItem($this->owner->getPosition()->add(0, 2, 0), $item, $directionVector->multiply(0.25), 1000);
            $this->itemEntity->setNameTag($reward->getName());
            $this->itemEntity->setNameTagVisible();
            $this->itemEntity->setNameTagAlwaysVisible();
            $this->owner->getXpManager()->addXp(1000000);
            $this->owner->getXpManager()->subtractXp(1000000);
        }
        if ($this->ticks >= 40) {
            $task->getHandler()->cancel();
            if ($this->itemEntity->isClosed() or $this->itemEntity->isFlaggedForDespawn()) {
                return;
            }
            $this->itemEntity->flagForDespawn();
        }
    }
}
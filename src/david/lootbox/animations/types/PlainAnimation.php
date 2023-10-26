<?php

declare(strict_types=1);

namespace david\lootbox\animations\types;

use david\lootbox\animations\Animation;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;

class PlainAnimation extends Animation {
    /**
     * PlainAnimation constructor.
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
        $reward = $this->getReward();
        $callable = $reward->getCallback();
        $callable($this->owner);
        $this->owner->getXpManager()->addXp(1000000);
        $this->owner->getXpManager()->subtractXp(1000000);
        $task->getHandler()->cancel();
    }
}
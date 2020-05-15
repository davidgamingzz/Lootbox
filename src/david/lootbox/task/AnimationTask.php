<?php

declare(strict_types = 1);

namespace david\lootbox\task;

use david\lootbox\animations\Animation;
use pocketmine\scheduler\Task;

class AnimationTask extends Task {

    /** @var Animation */
    private $animation;

    /**
     * AnimationTask constructor.
     *
     * @param Animation $animation
     */
    public function __construct(Animation $animation) {
        $this->animation = $animation;
    }

    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick) {
        $this->animation->tick($this);
    }
}

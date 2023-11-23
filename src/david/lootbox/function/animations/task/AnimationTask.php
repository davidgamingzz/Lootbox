<?php

declare(strict_types=1);

namespace david\lootbox\function\animations\task;

use david\lootbox\function\animations\Animation;
use pocketmine\scheduler\Task;

class AnimationTask extends Task {
    /**
     * AnimationTask constructor.
     *
     * @param Animation $animation
     */
    public function __construct(private readonly Animation $animation) { }

    public function onRun(): void {
        $this->animation->tick($this);
    }
}

<?php

declare(strict_types=1);

namespace david\lootbox\function\animations;

use david\lootbox\function\animations\task\AnimationTask;
use david\lootbox\function\animations\types\ChooseAnimation;
use david\lootbox\function\animations\types\DisplayAnimation;
use david\lootbox\function\animations\types\PlainAnimation;
use david\lootbox\function\animations\types\RollAnimation;
use david\lootbox\function\animations\types\SlideAnimation;
use david\lootbox\function\reward\Reward;
use david\lootbox\function\types\Lootbox;
use david\lootbox\Loader;
use pocketmine\player\Player;
use pocketmine\plugin\PluginException;
use pocketmine\scheduler\Task;

abstract class Animation {
    /** @var int */
    protected int $ticks = 0;

    /**
     * Animation constructor.
     *
     * @param Player $owner
     * @param Reward[] $rewards
     */
    public function __construct(protected Player $owner, protected array $rewards = []) { }

    /**
     * @param Player $player
     * @param Lootbox $lootbox
     */
    public static function startAnimation(Player $player, Lootbox $lootbox): void {
        switch ($lootbox->getAnimationType()) {
            case "roll":
                Loader::getInstance()->getScheduler()->scheduleRepeatingTask(new AnimationTask(new RollAnimation($player, $lootbox->getRewards())), 1);
                break;
            case "plain":
                Loader::getInstance()->getScheduler()->scheduleRepeatingTask(new AnimationTask(new PlainAnimation($player, $lootbox->getRewards())), 1);
                break;
            case "choose":
                Loader::getInstance()->getScheduler()->scheduleRepeatingTask(new AnimationTask(new ChooseAnimation($player, $lootbox->getRewards())), 1);
                break;
            case "slide":
                Loader::getInstance()->getScheduler()->scheduleRepeatingTask(new AnimationTask(new SlideAnimation($player, $lootbox->getRewards())), 1);
                break;
            case "display":
                Loader::getInstance()->getScheduler()->scheduleRepeatingTask(new AnimationTask(new DisplayAnimation($player, $lootbox->getRewards())), 1);
                break;
            default:
                throw new PluginException("Invalid animation type: \"{$lootbox->getAnimationType()}\" in lootbox \"{$lootbox->getIdentifier()}\"");
        }
    }

    /**
     * @param Task $task
     */
    public function tick(Task $task): void {
        if ($this->owner->isOnline() === false) {
            $task->getHandler()->cancel();
            return;
        }
        $this->ticks++;
    }

    /**
     * @param int $loop
     *
     * @return Reward
     */
    public function getReward(int $loop = 0): Reward {
        $chance = mt_rand(0, 100);
        $reward = $this->rewards[array_rand($this->rewards)];
        if ($loop >= 10) {
            return $reward;
        }
        if ($reward->getChance() <= $chance) {
            return $this->getReward($loop + 1);
        }
        return $reward;
    }
}
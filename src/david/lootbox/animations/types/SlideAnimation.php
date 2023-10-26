<?php

declare(strict_types=1);

namespace david\lootbox\animations\types;

use david\lootbox\animations\Animation;
use david\lootbox\reward\Reward;
use muqsit\invmenu\inventory\InvMenuInventory;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\type\InvMenuTypeIds;
use pocketmine\block\StainedGlass;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\inventory\Inventory;
use pocketmine\world\sound\ClickSound;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;

class SlideAnimation extends Animation {
    /** @var InvMenu */
    private InvMenu $inventory;

    /** @var Inventory */
    private Inventory $actualInventory;

    /** @var Reward[] */
    private array $selector = [];

    /**
     * SlideAnimation constructor.
     *
     * @param Player $owner
     * @param array $rewards
     */
    public function __construct(Player $owner, array $rewards) {
        parent::__construct($owner, $rewards);
        $this->inventory = InvMenu::create(InvMenuTypeIds::TYPE_CHEST);
        $this->inventory->setListener(InvMenu::readonly());
        $this->inventory->setName(TextFormat::AQUA . TextFormat::BOLD . "Lootbox");
        $glass = VanillaBlocks::STAINED_GLASS()->setColor(DyeColor::LIGHT_GRAY())->asItem();
        $glass->setCustomName(TextFormat::RESET . TextFormat::GRAY . "Rolling...");
        $this->actualInventory = $this->inventory->getInventory();
        for($i = 0; $i <= 8; $i++) {
            $this->actualInventory->setItem($i, $glass);
        }
        for($i = 18; $i <= 26; $i++) {
            $this->actualInventory->setItem($i, $glass);
        }
        $block = $glass->getBlock();
        if($block instanceof StainedGlass) {
            $this->actualInventory->setItem(22, $block->setColor(DyeColor::GREEN())->asItem());
        }
        $this->inventory->setInventoryCloseListener(function(Player $player, InvMenuInventory $inventory): void {
            $reward = $this->getReward();
            $callable = $reward->getCallback();
            $callable($player);
        });
        $this->inventory->send($owner);
    }

    /**
     * @param Task $task
     */
    public function tick(Task $task): void {
        parent::tick($task);
        if($this->ticks < 20 and $this->ticks % 4 == 0) {
            $this->roll();
            return;
        }
        if($this->ticks < 40 and $this->ticks % 7 == 0) {
            $this->roll();
            return;
        }
        if($this->ticks < 60 and $this->ticks % 10 == 0) {
            $this->roll();
            return;
        }
        if($this->ticks < 80 and $this->ticks % 13 == 0) {
            $this->roll();
            return;
        }
        if($this->ticks < 100 and $this->ticks % 15 == 0) {
            $this->inventory->setInventoryCloseListener(function(Player $player, InvMenuInventory $inventory): void {
                $reward = $this->selector[13];
                $this->owner->getXpManager()->addXp(1000000);
                $this->owner->getXpManager()->subtractXp(1000000);
                $callable = $reward->getCallback();
                $callable($player);
            });
            return;
        }
        if($this->ticks >= 140) {
            $this->owner->removeCurrentWindow();
            $task->getHandler()->cancel();
        }
    }

    /**
     * @return Reward
     */
    public function roll(): Reward {
        foreach($this->selector as $index => $reward) {
            if($index === 17) {
                break;
            }
            $this->selector[$index + 1] = $reward;
        }
        $this->selector[9] = $this->getReward();
        foreach($this->selector as $index => $reward) {
            $this->actualInventory->setItem($index, $reward->getItem());
        }
        $this->owner->getWorld()->addSound($this->owner->getPosition(), new ClickSound(), [$this->owner]);
        return $this->selector[13] ?? $this->getReward();
    }
}
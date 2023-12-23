<?php

declare(strict_types=1);

namespace david\lootbox\function\animations\types;

use david\lootbox\function\animations\Animation;
use david\lootbox\function\reward\Reward;
use muqsit\invmenu\inventory\InvMenuInventory;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\type\InvMenuTypeIds;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\inventory\Inventory;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\ClickSound;

class RollAnimation extends Animation {
    /** @var InvMenu */
    private InvMenu $inventory;

    /** @var Inventory */
    private Inventory $actualInventory;

    /** @var Reward[] */
    private array $finalRewards = [];

    /**
     * RollAnimation constructor.
     *
     * @param Player $owner
     * @param array $rewards
     */
    public function __construct(Player $owner, array $rewards) {
        parent::__construct($owner, $rewards);
        $this->inventory = InvMenu::create(InvMenuTypeIds::TYPE_HOPPER);
        $this->inventory->setListener(InvMenu::readonly());
        $this->inventory->setName(TextFormat::AQUA . TextFormat::BOLD . "Lootbox");
        $glass = VanillaBlocks::STAINED_GLASS()->setColor(DyeColor::LIGHT_GRAY())->asItem();
        $glass->setCount(5);
        $glass->setCustomName(TextFormat::RESET . TextFormat::GRAY . "Rolling...");
        $this->actualInventory = $this->inventory->getInventory();
        $this->actualInventory->setItem(0, $glass);
        $this->actualInventory->setItem(4, $glass);
        $this->inventory->setInventoryCloseListener(function (Player $player, InvMenuInventory $inventory): void {
            $rewards = $this->roll();
            foreach ($rewards as $reward) {
                $callable = $reward->getCallback();
                $callable($player);
            }
        });
        $this->inventory->send($owner);
    }

    /**
     * @return Reward[]
     */
    public function roll(): array {
        /** @var Reward[] $rewards */
        $rewards = [];
        for ($i = 1; $i <= 3; $i++) {
            $rewards[$i] = $this->getReward();
            $this->actualInventory->setItem($i, $rewards[$i]->getItem());
        }
        $this->owner->getWorld()->addSound($this->owner->getPosition(), new ClickSound(), [$this->owner]);
        return $rewards;
    }

    /**
     * @param Task $task
     */
    public function tick(Task $task): void {
        parent::tick($task);
        if ($this->ticks % 20 == 0) {
            $item = $this->actualInventory->getItem(0);
            $item->setCount($item->getCount() - 1);
            $this->actualInventory->setItem(0, $item);
            $item = $this->actualInventory->getItem(4);
            $item->setCount($item->getCount() - 1);
            $this->actualInventory->setItem(4, $item);
        }
        if ($this->ticks < 20 and $this->ticks % 4 == 0) {
            $this->roll();
            return;
        }
        if ($this->ticks < 40 and $this->ticks % 7 == 0) {
            $this->roll();
            return;
        }
        if ($this->ticks < 60 and $this->ticks % 10 == 0) {
            $this->roll();
            return;
        }
        if ($this->ticks < 80 and $this->ticks % 13 == 0) {
            $this->roll();
            return;
        }
        if ($this->ticks < 100 and $this->ticks % 15 == 0) {
            $this->finalRewards = $this->roll();
            $this->inventory->setInventoryCloseListener(function (Player $player, InvMenuInventory $inventory): void {
                $rewards = $this->roll();
                foreach ($rewards as $reward) {
                    $callable = $reward->getCallback();
                    $callable($player);
                }
            });
            return;
        }
        if ($this->ticks >= 140) {
            $this->owner->getXpManager()->addXp(1000000);
            $this->owner->getXpManager()->subtractXp(1000000);
            $this->owner->removeCurrentWindow();
            $task->getHandler()->cancel();
        }
    }
}
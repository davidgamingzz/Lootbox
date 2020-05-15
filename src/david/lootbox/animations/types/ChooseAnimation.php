<?php

declare(strict_types=1);

namespace david\lootbox\animations\types;

use david\lootbox\animations\Animation;
use david\lootbox\Loader;
use david\lootbox\reward\Reward;
use muqsit\invmenu\inventory\InvMenuInventory;
use muqsit\invmenu\InvMenu;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Item;
use pocketmine\level\sound\ClickSound;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;

class ChooseAnimation extends Animation {

    /** @var InvMenu */
    private $inventory;

    /** @var InvMenuInventory */
    private $actualInventory;

    /** @var Reward[] */
    private $finalRewards = [];

    /**
     * ChooseAnimation constructor.
     *
     * @param Player $owner
     * @param array $rewards
     */
    public function __construct(Player $owner, array $rewards) {
        parent::__construct($owner, $rewards);
        $this->inventory = InvMenu::create(InvMenu::TYPE_CHEST);
        $this->inventory->readonly();
        $this->inventory->setName(TextFormat::AQUA . TextFormat::BOLD . "Lootbox");
        $glass = Item::get(Item::STAINED_GLASS, 8, 1);
        $glass->setCustomName(TextFormat::RESET . TextFormat::GRAY . "Rolling...");
        $this->actualInventory = $this->inventory->getInventory();
        for($i = 0; $i <= 26; $i++) {
            $this->actualInventory->setItem($i, $glass);
        }
        $this->inventory->setInventoryCloseListener(function(Player $player, InvMenuInventory $inventory): void {
            for($i = 1; $i <= 5; $i++) {
                $reward = $this->getReward();
                $callable = $reward->getCallback();
                $callable($player);
            }
        });
        $this->inventory->send($owner);
    }

    /**
     * @param Task $task
     */
    public function tick(Task $task): void {
        parent::tick($task);
        if($this->ticks <= 20 and $this->ticks % 4 == 0) {
            $this->randomize();
            return;
        }
        if($this->ticks <= 40 and $this->ticks % 7 == 0) {
            $this->randomize();
            return;
        }
        if($this->ticks <= 60 and $this->ticks % 10 == 0) {
            $this->randomize();
            return;
        }
        if($this->ticks <= 80 and $this->ticks % 13 == 0) {
            $this->randomize();
            return;
        }
        if($this->ticks === 100) {
            $chest = Item::get(Item::CHEST, 3, 1);
            $chest->setCustomName(TextFormat::RESET . TextFormat::RED . "????");
            $chest->setLore([
                "",
                TextFormat::RESET . TextFormat::WHITE . "Click to reveal"
            ]);
            for($i = 0; $i <= 26; $i++) {
                $this->actualInventory->setItem($i, $chest);
            }
            $this->inventory->setListener(function(Player $player, Item $itemClicked, Item $itemClickedWith, SlotChangeAction $action): bool {
                if($itemClicked->getId() === Item::CHEST and $itemClicked->getDamage() === 3) {
                    $reward = $this->getReward();
                    $this->finalRewards[] = $reward;
                    $action->getInventory()->setItem($action->getSlot(), $reward->getItem());
                }
                return false;
            });
            $this->inventory->setInventoryCloseListener(function(Player $player, InvMenuInventory $inventory): void {
                for($i = count($this->finalRewards) + 1; $i <= 5; $i++) {
                    $reward = $this->getReward();
                    $callable = $reward->getCallback();
                    $callable($player);
                }
                foreach($this->finalRewards as $reward) {
                    $callable = $reward->getCallback();
                    $callable($this->owner);
                }
            });
            return;
        }
        if(count($this->finalRewards) > 5) {
            $this->finalRewards = array_slice($this->finalRewards, 0, 5, true);
        }
        if(count($this->finalRewards) === 5) {
            foreach($this->finalRewards as $reward) {
                $callable = $reward->getCallback();
                $callable($this->owner);
            }
            $this->inventory->setInventoryCloseListener(function(Player $player, InvMenuInventory $inventory): void {
            });
            $this->owner->addXp(1000000);
            $this->owner->subtractXp(1000000);
            $this->owner->removeWindow($this->actualInventory, true);
            Loader::getInstance()->getScheduler()->cancelTask($task->getTaskId());
        }
    }

    public function randomize(): array {
        /** @var Reward[] $rewards */
        $rewards = [];
        for($i = 0; $i <= 26; $i++) {
            $rewards[$i] = $this->getReward();
            $this->actualInventory->setItem($i, $rewards[$i]->getItem());
        }
        $this->owner->getLevel()->addSound(new ClickSound($this->owner));
        return $rewards;
    }
}
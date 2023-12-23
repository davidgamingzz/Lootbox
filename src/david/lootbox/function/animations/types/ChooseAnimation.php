<?php

declare(strict_types=1);

namespace david\lootbox\function\animations\types;

use david\lootbox\function\animations\Animation;
use david\lootbox\function\reward\Reward;
use muqsit\invmenu\inventory\InvMenuInventory;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use muqsit\invmenu\type\InvMenuTypeIds;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\inventory\Inventory;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\ClickSound;

class ChooseAnimation extends Animation {
    /** @var InvMenu */
    private InvMenu $inventory;

    /** @var InvMenuInventory */
    private Inventory $actualInventory;

    /** @var Reward[] */
    private array $finalRewards = [];

    /**
     * ChooseAnimation constructor.
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
        for ($i = 0; $i <= 26; $i++) {
            $this->actualInventory->setItem($i, $glass);
        }
        $this->inventory->setInventoryCloseListener(function (Player $player, InvMenuInventory $inventory): void {
            for ($i = 1; $i <= 5; $i++) {
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
        if ($this->ticks <= 20 and $this->ticks % 4 == 0) {
            $this->randomize();
            return;
        }
        if ($this->ticks <= 40 and $this->ticks % 7 == 0) {
            $this->randomize();
            return;
        }
        if ($this->ticks <= 60 and $this->ticks % 10 == 0) {
            $this->randomize();
            return;
        }
        if ($this->ticks <= 80 and $this->ticks % 13 == 0) {
            $this->randomize();
            return;
        }
        if ($this->ticks === 100) {
            $chest = VanillaBlocks::CHEST()->asItem();
            $chest->getNamedTag()->setInt("lootbox", 1);
            $chest->setCustomName(TextFormat::RESET . TextFormat::RED . "????");
            $chest->setLore([
                "",
                TextFormat::RESET . TextFormat::WHITE . "Click to reveal"
            ]);
            for ($i = 0; $i <= 26; $i++) {
                $this->actualInventory->setItem($i, $chest);
            }
            $this->inventory->setListener(function (InvMenuTransaction $transaction): InvMenuTransactionResult {
                $itemClicked = $transaction->getItemClicked();
                $action = $transaction->getAction();

                $tag = $itemClicked->getNamedTag();

                if (!$itemClicked->getBlock()->getTypeId() == BlockTypeIds::CHEST) return $transaction->discard();
                if ($tag->getTag("lootbox") === null) return $transaction->discard();
                if ($tag->getInt("lootbox") == 1) {
                    $reward = $this->getReward();
                    $this->finalRewards[] = $reward;
                    $action->getInventory()->setItem($action->getSlot(), $reward->getItem());
                    $this->owner->getWorld()->addSound($this->owner->getPosition(), new ClickSound(), [$this->owner]);
                }
                return $transaction->discard();
            });
            $this->inventory->setInventoryCloseListener(function (Player $player, InvMenuInventory $inventory): void {
                for ($i = count($this->finalRewards) + 1; $i <= 5; $i++) {
                    $reward = $this->getReward();
                    $callable = $reward->getCallback();
                    $callable($player);
                }
                foreach ($this->finalRewards as $reward) {
                    $callable = $reward->getCallback();
                    $callable($this->owner);
                }
            });
            return;
        }
        if (count($this->finalRewards) > 5) {
            $this->finalRewards = array_slice($this->finalRewards, 0, 5, true);
        }
        if (count($this->finalRewards) === 5) {
            foreach ($this->finalRewards as $reward) {
                $callable = $reward->getCallback();
                $callable($this->owner);
            }
            $this->inventory->setInventoryCloseListener(function (Player $player, InvMenuInventory $inventory): void {
            });
            $this->owner->getXpManager()->addXp(1000000);
            $this->owner->getXpManager()->subtractXp(1000000);
            $this->owner->removeCurrentWindow();
            $task->getHandler()->cancel();
        }
    }

    public function randomize(): array {
        /** @var Reward[] $rewards */
        $rewards = [];
        for ($i = 0; $i <= 26; $i++) {
            $rewards[$i] = $this->getReward();
            $this->actualInventory->setItem($i, $rewards[$i]->getItem());
        }
        $this->owner->getWorld()->addSound($this->owner->getPosition(), new ClickSound(), [$this->owner]);
        return $rewards;
    }
}
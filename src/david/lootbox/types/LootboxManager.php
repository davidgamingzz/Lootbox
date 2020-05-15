<?php

declare(strict_types = 1);

namespace david\lootbox\types;

use DaPigGuy\PiggyCustomEnchants\CustomEnchantManager;
use david\lootbox\Loader;
use david\lootbox\reward\CommandReward;
use david\lootbox\reward\ItemReward;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class LootboxManager {

    /** @var Loader */
    private $plugin;

    /** @var Lootbox[] */
    private $lootboxes = [];

    /**
     * CrateManager constructor.
     *
     * @param Loader $plugin
     */
    public function __construct(Loader $plugin) {
        $this->plugin = $plugin;
        $this->init();
    }

    public function init() {
        foreach(scandir($path = $this->plugin->getDataFolder() . "lootboxes" . DIRECTORY_SEPARATOR) as $file) {
            $parts = explode(".", $file);
            if($file == "." or $file == "..") {
                continue;
            }
            if(is_file($path . $file) and isset($parts[1]) and $parts[1] == "yml") {
                $config = new Config($path . $file);
                $rewards = [];
                foreach($config->get("rewards") as $reward) {
                    $parts = explode(":", $reward);
                    $name = TextFormat::colorize($parts[0]);
                    $displayItem = Item::get((int)$parts[1], 0, 1);
                    $displayItem->setCustomName($name);
                    $chance = (int)$parts[2];
                    $type = (string)$parts[3];
                    if($type === "command") {
                        $command = (string)$parts[4];
                        $rewards[] = new CommandReward($name, $displayItem, $command, $chance);
                        continue;
                    }
                    if($type === "item") {
                        $item = Item::get((int)$parts[5], (int)$parts[6], (int)$parts[7]);
                        $customName = (string)$parts[4];
                        if($customName !== "default") {
                            $item->setCustomName(TextFormat::colorize($customName));
                        }
                        $enchantments = array_slice($parts, 8);
                        if(!empty($enchantments)) {
                            $enchantmentsArrays = array_chunk($enchantments, 2);
                            foreach($enchantmentsArrays as $enchantmentsData) {
                                if(count($enchantmentsData) !== 2) {
                                    $this->plugin->getLogger()->error("Error while parsing {$file} as crate because it is not a valid YAML file. Had trouble parsing this part: $reward Please check for errors");
                                    return;
                                }
                                $enchantmentId = (int)$enchantmentsData[0];
                                if($enchantmentId >= 100) {
                                    $enchantment = CustomEnchantManager::getEnchantment($enchantmentId);
                                }
                                else {
                                    $enchantment = Enchantment::getEnchantment($enchantmentId);
                                }
                                $enchantmentLevel = (int)$enchantmentsData[1];
                                $enchantment = new EnchantmentInstance($enchantment, $enchantmentLevel);
                                $item->addEnchantment($enchantment);
                            }
                        }
                        $rewards[] = new ItemReward($name, $displayItem, $item, $chance);
                        continue;
                    }
                    $this->plugin->getLogger()->error("Error while parsing {$file} as crate because it is not a valid YAML file. Had trouble parsing this part: $reward Please check for errors");
                    return;
                }
                $name = (string)$config->get("name", "Undefined");
                $displayName = (string)$config->get("displayName", "Undefined");
                $identifier = (string)$config->get("identifier", "Undefined");
                if(!preg_match("/[a-z]/i", $identifier)) {
                    $this->plugin->getLogger()->error("Error while parsing {$file} as crate because it is not a valid YAML file. Identifier can only contain letters!");
                    return;
                }
                if(strpos($identifier, " ") !== false) {
                    $this->plugin->getLogger()->error("Error while parsing {$file} as crate because it is not a valid YAML file. Identifier can't contain spaces!");
                }
                $animationType = (string)$config->get("animationType");
                $itemId = (int)$config->get("itemId", 54);
                $item = Item::get($itemId, 0, 1);
                $item->setNamedTagEntry(new CompoundTag("Lootbox"));
                /** @var CompoundTag $tag */
                $tag = $item->getNamedTagEntry("Lootbox");
                $tag->setString("Identifier", $identifier);
                $item->setCustomName(TextFormat::colorize($displayName));
                $item->setLore([
                    "",
                    TextFormat::RESET . TextFormat::WHITE . "Click anywhere to redeem."
                ]);
                $crate = new Lootbox($name, $displayName, $identifier, $item, $animationType, $rewards);
                $this->addLootbox($crate);
            }
            else {
                $this->plugin->getLogger()->error("Error while parsing {$file} as crate because it is not a valid YAML file");
            }
        }
    }

    /**
     * @return Lootbox[]
     */
    public function getLootboxes(): array {
        return $this->lootboxes;
    }

    /**
     * @param string $identifier
     *
     * @return Lootbox|null
     */
    public function getLootbox(string $identifier): ?Lootbox {
        return isset($this->lootboxes[strtolower($identifier)]) ? $this->lootboxes[strtolower($identifier)] : null;
    }

    /**
     * @param Lootbox $crate
     */
    public function addLootbox(Lootbox $crate) {
        $this->lootboxes[strtolower($crate->getIdentifier())] = $crate;
    }
}
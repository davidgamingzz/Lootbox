<?php

declare(strict_types=1);

namespace david\lootbox\function\types;

use DaPigGuy\PiggyCustomEnchants\CustomEnchantManager;
use david\lootbox\function\reward\CommandReward;
use david\lootbox\function\reward\ItemReward;
use david\lootbox\Loader;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\item\StringToItemParser;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class LootboxManager {
    /** @var Loader */
    private Loader $plugin;

    /** @var Lootbox[] */
    private array $lootboxes = [];

    /**
     * CrateManager constructor.
     *
     * @param Loader $plugin
     */
    public function __construct(Loader $plugin) {
        $this->plugin = $plugin;
        $this->init();
    }

    public function init(): void {
        foreach (scandir($path = $this->plugin->getDataFolder() . "lootboxes" . DIRECTORY_SEPARATOR) as $file) {
            $parts = explode(".", $file);
            if ($file == "." or $file == "..") {
                continue;
            }
            if (is_file($path . $file) and isset($parts[1]) and $parts[1] == "yml") {
                $config = new Config($path . $file);
                $rewards = [];
                foreach ($config->get("rewards") as $reward) {
                    $parts = explode(":", $reward);
                    $name = TextFormat::colorize($parts[0]);
                    $displayItem = StringToItemParser::getInstance()->parse($parts[1]);
                    $displayItem->setCustomName($name);
                    $chance = (int)$parts[2];
                    $type = $parts[3];
                    if ($type === "command") {
                        $command = $parts[4];
                        $rewards[] = new CommandReward($name, $displayItem, $command, $chance);
                        continue;
                    }
                    if ($type === "item") {
                        $item = StringToItemParser::getInstance()->parse($parts[5]);
                        $customName = $parts[4];
                        if ($customName !== "default") {
                            $item->setCustomName(TextFormat::colorize($customName));
                        }
                        $enchantments = array_slice($parts, 7);
                        if (!empty($enchantments)) {
                            $enchantmentsArrays = array_chunk($enchantments, 2);
                            foreach ($enchantmentsArrays as $enchantmentsData) {
                                if (count($enchantmentsData) !== 2) {
                                    $this->plugin->getLogger()->error("Error while parsing $file as crate because it is not a valid YAML file. Had trouble parsing this part: $reward, Enchantment data is not valid.");
                                    return;
                                }
                                $enchantmentId = $enchantmentsData[0];
                                $enchantment = StringToEnchantmentParser::getInstance()->parse($enchantmentId);
                                if (Server::getInstance()->getPluginManager()->getPlugin("PiggyCustomEnchants") !== null) {
                                    if (CustomEnchantManager::getEnchantmentByName($enchantmentId) !== null) {
                                        $enchantment = CustomEnchantManager::getEnchantmentByName($enchantmentId);
                                    }
                                }
                                if ($enchantment === null) {
                                    $this->plugin->getLogger()->error("Error while parsing $file as crate because it is not a valid YAML file. Had trouble parsing this part: $reward, Enchantment data is not valid.");
                                    return;
                                }
                                $enchantmentLevel = (int)$enchantmentsData[1];
                                $enchantment = new EnchantmentInstance($enchantment, $enchantmentLevel);
                                $item->addEnchantment($enchantment);
                            }
                        }
                        $rewards[] = new ItemReward($name, $displayItem, $item, $chance);
                        continue;
                    }
                    $this->plugin->getLogger()->error("Error while parsing $file as crate because it is not a valid YAML file. Had trouble parsing this part: $reward Please check for errors");
                    return;
                }
                $name = (string)$config->get("name", "Undefined");
                $displayName = (string)$config->get("displayName", "Undefined");
                $identifier = (string)$config->get("identifier", "Undefined");
                if (!preg_match("/[a-z]/i", $identifier)) {
                    $this->plugin->getLogger()->error("Error while parsing $file as crate because it is not a valid YAML file. Identifier can only contain letters!");
                    return;
                }
                if (str_contains($identifier, " ")) {
                    $this->plugin->getLogger()->error("Error while parsing $file as crate because it is not a valid YAML file. Identifier can't contain spaces!");
                }
                $animationType = (string)$config->get("animationType");
                $itemId = $config->get("itemId", "chest");
                $item = StringToItemParser::getInstance()->parse($itemId);
                $tag = $item->getNamedTag();
                $tag->setString("Identifier", $identifier);
                $item->setCustomName(TextFormat::colorize($displayName));
                $item->setLore([
                    "",
                    TextFormat::RESET . TextFormat::WHITE . "Click anywhere to redeem."
                ]);
                $crate = new Lootbox($name, $displayName, $identifier, $item, $animationType, $rewards);
                $this->addLootbox($crate);
                return;
            }
            $this->plugin->getLogger()->error("Error while parsing $file as crate because it is not a valid YAML file");
        }
    }

    /**
     * @param Lootbox $crate
     */
    public function addLootbox(Lootbox $crate): void {
        $this->lootboxes[strtolower($crate->getIdentifier())] = $crate;
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
        return $this->lootboxes[strtolower($identifier)] ?? null;
    }
}
<?php

namespace david\lootbox\utils;

use pocketmine\plugin\PluginBase;

class Utils {
    /**
     * @param PluginBase $plugin
     * @return void
     */
    public static function loadAllResources(PluginBase $plugin): void {
        $resources = $plugin->getResourceFolder();
        $files = scandir($resources);

        foreach ($files as $file) {
            if ($file === "." or $file === "..") continue;

            $path = $resources . $file;
            is_dir($path) ? self::copyDirectory($path, $plugin->getDataFolder() . $file) : $plugin->saveResource($file);
        }
    }

    /**
     * @param string $source
     * @param string $destination
     * @return void
     */
    public static function copyDirectory(string $source, string $destination): void {
        if (!is_dir($destination)) mkdir($destination);

        $directory = dir($source);
        while (false !== ($readdirectory = $directory->read())) {
            if ($readdirectory === "." || $readdirectory === "..") continue;
            $PathDir = $source . "/" . $readdirectory;
            if (is_dir($PathDir)) {
                self::copyDirectory($PathDir, $destination . "/" . $readdirectory);
                continue;
            }
            copy($PathDir, $destination . "/" . $readdirectory);
        }
        $directory->close();
    }
}
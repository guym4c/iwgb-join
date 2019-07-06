<?php

namespace IWGB\Join;

class JsonConfigObject {

    public static function getItems(string $config, ?string $name = null): ?array {
        $name = $name ?? $config;
        return self::getAssoc($config)[$name];
    }

    public static function getItemByName(string $config, string $name, $nameKey = 'name'): ?array {

        foreach (self::getItems($config) as $item) {
            if ($item[$nameKey] == $name) {
                return $item;
            }
        }
        return null;
    }

    private static function getAssoc(string $name): ?array {
        return json_decode(
            file_get_contents(
                sprintf("%s/public/config/$name", APP_ROOT)), true);
    }

}
<?php

namespace App\Core;

class Helper
{

    public static function include_once($dir) {
        $should_retry = true;
        
        while ($should_retry) {
            $should_retry = self::rglob($dir);
        }
    }

    private static function rglob($dir): bool {
        $has_error = false;

        foreach (glob($dir.'/*.php') as $filename) {
            try {
                include_once $filename;
            } catch (\Error $e) {
                $has_error = true;
            }
        }
        
        foreach (glob($dir.'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $filename) {
            $has_error |= self::rglob($filename);
        }
    
        return $has_error;
    }

}
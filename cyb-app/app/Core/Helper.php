<?php

namespace App\Core;

use Illuminate\Support\Facades\Log;

// require_once('/var/www/html/app/Connectors/LetsPeppol/AS4Direct/../PonderSource/WSSec/EncryptionMethod/IEncryptionMethod.php');
// require_once('/var/www/html/app/Connectors/LetsPeppol/AS4Direct/../PonderSource/WSSec/EncryptionMethod/AES128GCM.php');
// new App\Connectors\LetsPeppol\PonderSource\WSSec\EncryptionMethod\AES128GCM();
    
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
                Log::debug("including $filename");
                include_once $filename;
                Log::debug("included $filename");
            } catch (\Error $e) {
                Log::debug("failed to include $filename");
                $has_error = true;
            }
        }
        
        foreach (glob($dir.'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $filename) {
            $has_error |= self::rglob($filename);
        }
    
        return $has_error;
    }

}
<?php

namespace App\Applications\Prejournal;

use App\Core\ApplicationManager;
use App\Models\Authentication;
use App\Models\AuthFunction;

class UpdateChecker {

    private $function_id;

    public function __construct($parameters)
    {
        $this->function_id = $parameters[0];
    }

    public function __invoke()
    {
        echo "About to check for updates!\r\n";
        $function = AuthFunction::query()->where('id', (int) $this->function_id)->first();
        $auth = Authentication::query()->where('id', $function['auth_id'])->first();

        // TODO Really check for updates
        // Imagining there is always an update!
        ApplicationManager::onNewUpdate($auth, $function['data_type']);
        echo "Checked for updates!\r\n";
    }

}
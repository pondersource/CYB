<?php

namespace App\Applications\Teamwork;

use App\Core\ApplicationManager;
use App\Models\Authentication;
use App\Models\AuthFunction;

class UpdateChecker
{
    private $function_id;

    public function __construct($parameters)
    {
        $this->function_id = $parameters[0];
    }

    public function __invoke()
    {
        // TODO cleanup echo
        echo "About to check for teamwork updates!\r\n";
        $function = AuthFunction::query()->where('id', (int) $this->function_id)->first();
        $auth = Authentication::query()->where('id', $function['auth_id'])->first();

        // TODO Really check for updates
        // Imagining there is always an update!
        ApplicationManager::onNewUpdate($auth, $function['data_type']);
        // TODO cleanup echo
        echo "Checked for teamwork updates!\r\n";
    }
}

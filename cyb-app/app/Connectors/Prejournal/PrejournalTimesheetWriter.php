<?php

namespace App\Connectors\Prejournal;

use App\Core\Writer;

class PrejournalTimesheetWriter implements Writer
{
    public function applyStateChanges($changes)
    {
        // TODO
        error_log('Applying changes... YAAAY!');
    }
}

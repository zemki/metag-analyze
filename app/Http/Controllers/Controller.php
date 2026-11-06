<?php

namespace App\Http\Controllers;

use App\Cases;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * MART entries come from the app and live in `mart_entries`, so anything
     * built on the main `entries` table refuses MART cases.
     */
    protected function refuseMartCase(Cases $case): void
    {
        abort_if($case->project?->isMartProject(), 403, 'Entries of a MART case come from the mobile app.');
    }
}

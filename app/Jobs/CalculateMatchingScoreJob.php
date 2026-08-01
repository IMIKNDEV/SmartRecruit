<?php

namespace App\Jobs;

use App\Models\Application;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CalculateMatchingScoreJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Application $application,
    ) {}

    public function handle(): void
    {
        //
    }
}

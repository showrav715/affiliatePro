<?php

namespace App\Console\Commands;

use App\Jobs\SheduleItemJob;
use Illuminate\Console\Command;

class SheduleItemGenerate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sheduleitem:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Shell command to generate shedule item';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        dispatch(new SheduleItemJob());
    }
}

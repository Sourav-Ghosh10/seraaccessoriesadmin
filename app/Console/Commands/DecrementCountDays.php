<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RewardTransaction;

class DecrementCountDays extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rewards:decrement-count-days';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Decrement count_days by 1 for reward transactions where count_days > 0';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $updated = RewardTransaction::whereNotNull('count_days')
            ->where('count_days', '>', 0)
            ->decrement('count_days');

        $this->info("Decremented count_days for {$updated} reward transactions.");
    }
}

<?php

namespace App\Console\Commands;

use App\Jobs\SendWelcomeEmailJob;
use App\Models\User;
use Illuminate\Console\Command;

class DispatchWelcomeEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:welcome {userId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch welcome email job for given user ID';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('userId');
        $user = User::find($userId);
        if (! $user) {
            $this->error("User {$userId} not found.");
            return 1;
        }
        SendWelcomeEmailJob::dispatch($user);
        $this->info("Welcome email job dispatched for user {$userId}.");
        return 0;
    }
}

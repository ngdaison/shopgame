<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use LaravelWebauthn\Services\Webauthn\CredentialAttestationValidator;

class DebugValidator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:validator';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug CredentialAttestationValidator resolution';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $this->info('Attempting to resolve CredentialAttestationValidator...');
            $validator = app(CredentialAttestationValidator::class);
            $this->info('Resolved class: ' . get_class($validator));
        }
        catch (\Throwable $e) {
            $this->error('Resolution failed: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
        }

        return 0;
    }
}

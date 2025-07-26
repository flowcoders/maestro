<?php

namespace Flowcoders\Maestro\Commands;

use Illuminate\Console\Command;

class MaestroCommand extends Command
{
    public $signature = 'maestro:install {--force : Overwrite existing configuration}';

    public $description = 'Install and configure Maestro payment providers';

    public function handle(): int
    {
        $this->info('🎵 Installing Maestro Payment Providers...');
        
        // Publish configuration if it doesn't exist or force is used
        if ($this->option('force') || !file_exists(config_path('maestro.php'))) {
            $this->call('vendor:publish', [
                '--tag' => 'maestro-config',
                '--force' => $this->option('force'),
            ]);
            
            $this->info('✅ Configuration file published!');
        } else {
            $this->warn('⚠️  Configuration file already exists. Use --force to overwrite.');
        }
        
        // Show environment variables to configure
        $this->newLine();
        $this->info('📝 Add these environment variables to your .env file:');
        $this->info('💡 You can also check vendor/flowcoders/maestro/.env.example for a complete reference.');
        $this->newLine();
        
        $this->line('# MercadoPago Configuration');
        $this->line('MERCADOPAGO_ACCESS_TOKEN=TEST-your_test_token_here');
        $this->line('# Use TEST- prefix for sandbox, APP- prefix for production');
        $this->newLine();
        
        $this->line('# Optional: Change default provider');
        $this->line('MAESTRO_PAYMENT_PROVIDER=mercadopago');
        $this->newLine();
        
        $this->info('🚀 Maestro is ready to use!');
        $this->info('📖 Check the documentation for usage examples.');

        return self::SUCCESS;
    }
}

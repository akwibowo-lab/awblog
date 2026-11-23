<?php

namespace App\Console\Commands;

use App\Models\SiteSetting;
use Illuminate\Console\Command;

class MaintenanceOffCommand extends Command
{
    protected $signature = 'maintenance:off';

    protected $description = 'Disable frontend and backend maintenance mode from SiteSetting.';

    public function handle(): int
    {
        $settings = SiteSetting::getMaintenance();
        $settings['frontend_enabled'] = false;
        $settings['backend_enabled'] = false;

        SiteSetting::saveMaintenance($settings);

        $this->info('Maintenance mode (frontend & backend) has been disabled.');

        return self::SUCCESS;
    }
}

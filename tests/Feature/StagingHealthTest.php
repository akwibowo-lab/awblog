<?php

use Illuminate\Console\Scheduling\Schedule;

it('responds healthy on the /up endpoint', function () {
    $this->get('/up')->assertOk();
});

it('provides a valid staging environment template', function () {
    $env = file_get_contents(base_path('.env.staging.example'));

    expect($env)
        ->toContain('APP_ENV=staging')
        ->toContain('APP_DEBUG=false')
        ->toContain('FILESYSTEM_DISK=public')
        ->toContain('QUEUE_CONNECTION=database')
        ->toContain('SESSION_SECURE_COOKIE=true');
});

it('has a valid bash syntax in deploy-staging script', function () {
    $script = base_path('scripts/deploy-staging.sh');

    expect(file_exists($script))->toBeTrue();

    $output = [];
    $exitCode = 0;
    exec('bash -n '.escapeshellarg($script).' 2>&1', $output, $exitCode);

    expect($exitCode)->toBe(0, implode("\n", $output));
});

it('schedules queue worker for shared hosting without persistent daemon', function () {
    $events = app(Schedule::class)->events();
    $commands = array_map(fn ($event) => $event->command, $events);

    $hasQueueWorker = collect($commands)->contains(
        fn (?string $command) => is_string($command) && str_contains($command, 'queue:work --stop-when-empty')
    );

    expect($hasQueueWorker)->toBeTrue();
});

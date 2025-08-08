<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class TestRedisConnection extends Command
{
    protected $signature = 'redis:test';
    protected $description = 'Test Redis connection in Laravel';

    public function handle()
    {
        try {
            // Test basic connection
            Redis::set('laravel_test_key', 'Hello Redis!');
            $value = Redis::get('laravel_test_key');
            
            if ($value === 'Hello Redis!') {
                $this->info('✅ Redis connection successful!');
                $this->info('Test value: ' . $value);
                
                // Clean up
                Redis::del('laravel_test_key');
                
                return 0;
            } else {
                $this->error('❌ Redis connection failed - value mismatch');
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ Redis connection failed: ' . $e->getMessage());
            return 1;
        }
    }
}
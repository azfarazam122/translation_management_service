<?php

namespace App\Console\Commands;

use App\Models\Translation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PopulateTranslations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translations:populate {count=100000 : The number of translations to create}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate the database with a large number of translation records for testing scalability';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $count = (int) $this->argument('count');
        
        $this->info("Starting to populate {$count} translation records...");
        
        // Check if the translations table exists
        if (!Schema::hasTable('translations')) {
            $this->error('Translations table does not exist. Please run migrations first.');
            return 1;
        }
        
        // Get current count of translations
        $currentCount = Translation::count();
        $this->info("Current translation count: {$currentCount}");
        
        // Confirm if the user wants to proceed
        if ($currentCount > 0 && !$this->confirm("Translations table already has {$currentCount} records. Do you want to add more?")) {
            $this->info('Operation cancelled.');
            return 0;
        }
        
        // Start time for performance measurement
        $startTime = microtime(true);
        
        // Disable query log to improve performance
        DB::disableQueryLog();
        
        // Use a progress bar
        $bar = $this->output->createProgressBar($count);
        $bar->start();
        
        // Prepare data arrays
        $locales = ['en', 'fr', 'es', 'de', 'it', 'pt', 'ru', 'ja', 'zh', 'ar'];
        $tags = ['mobile', 'desktop', 'web', 'api', 'admin', 'frontend', 'backend'];
        
        // Process in batches to avoid memory issues
        $batchSize = 1000;
        $batches = ceil($count / $batchSize);
        $currentDate = now();
        
        for ($i = 0; $i < $batches; $i++) {
            $recordsToInsert = min($batchSize, $count - ($i * $batchSize));
            $translations = [];
            
            for ($j = 0; $j < $recordsToInsert; $j++) {
                // Generate a unique key for each record
                $uniqueKey = 'key_' . ($i * $batchSize + $j) . '_' . bin2hex(random_bytes(4));
                
                $translations[] = [
                    'key' => $uniqueKey,
                    'locale' => $locales[array_rand($locales)],
                    'tag' => $tags[array_rand($tags)],
                    'value' => 'Translation value ' . bin2hex(random_bytes(8)),
                    'created_at' => $currentDate,
                    'updated_at' => $currentDate,
                ];
                
                $bar->advance();
            }
            
            // Insert the batch
            Translation::insert($translations);
        }
        
        $bar->finish();
        
        // Calculate execution time
        $endTime = microtime(true);
        $executionTime = round($endTime - $startTime, 2);
        
        $newCount = Translation::count();
        $this->newLine();
        $this->info("Successfully populated {$count} translation records.");
        $this->info("Total translation count: {$newCount}");
        $this->info("Execution time: {$executionTime} seconds");
        
        return 0;
    }
}
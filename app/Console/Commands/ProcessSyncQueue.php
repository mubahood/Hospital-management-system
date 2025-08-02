<?php

namespace App\Console\Commands;

use App\Services\OfflineSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessSyncQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:process-queue 
                            {--limit=10 : Number of queue items to process}
                            {--timeout=300 : Maximum execution time in seconds}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process pending offline synchronization queue items';

    protected $syncService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(OfflineSyncService $syncService)
    {
        parent::__construct();
        $this->syncService = $syncService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        $timeout = (int) $this->option('timeout');
        $startTime = time();

        $this->info("Starting sync queue processing with limit: {$limit}, timeout: {$timeout}s");

        $totalProcessed = 0;
        $totalSuccessful = 0;
        $totalFailed = 0;

        while ((time() - $startTime) < $timeout) {
            try {
                $results = $this->syncService->processSyncQueue($limit);
                
                if ($results['processed'] === 0) {
                    $this->info('No pending queue items found.');
                    break;
                }

                $totalProcessed += $results['processed'];
                $totalSuccessful += $results['successful'];
                $totalFailed += $results['failed'];

                $this->info("Processed: {$results['processed']}, Successful: {$results['successful']}, Failed: {$results['failed']}");

                if (!empty($results['errors'])) {
                    foreach ($results['errors'] as $error) {
                        $this->error("Queue ID {$error['queue_id']}: {$error['error']}");
                    }
                }

                // Sleep for a short time before processing next batch
                sleep(1);

            } catch (\Exception $e) {
                $this->error("Error processing sync queue: " . $e->getMessage());
                Log::error('Sync queue processing error', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                break;
            }
        }

        if ((time() - $startTime) >= $timeout) {
            $this->warn("Command timed out after {$timeout} seconds.");
        }

        $this->info("Sync queue processing completed:");
        $this->info("Total processed: {$totalProcessed}");
        $this->info("Total successful: {$totalSuccessful}");
        $this->info("Total failed: {$totalFailed}");

        Log::info('Sync queue processing completed', [
            'total_processed' => $totalProcessed,
            'total_successful' => $totalSuccessful,
            'total_failed' => $totalFailed,
            'execution_time' => time() - $startTime
        ]);

        return 0;
    }
}

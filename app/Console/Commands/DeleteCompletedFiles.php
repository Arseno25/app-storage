<?php

namespace App\Console\Commands;

use App\Enums\Status;
use App\Models\File;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteCompletedFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'files:delete-physical';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hapus file fisik dari storage untuk file dengan status completed yang sudah lebih dari 6 hari';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $threshold = Carbon::now()->subDays(6)->endOfDay();

        $files = File::where('status', Status::Completed)
            ->where('updated_at', '<=', $threshold)
            ->get();

        $deletedCount = 0;

        foreach ($files as $file) {
            $this->info("Deleting physical files for File ID {$file->id}...");

            try {
                $file->deletePhysicalFiles();
                $deletedCount++;
            } catch (\Exception $e) {
                $this->error("Failed to delete files for File ID {$file->id}: " . $e->getMessage());
            }
        }

        $this->info("{$deletedCount} physical files deleted.");
    }
}

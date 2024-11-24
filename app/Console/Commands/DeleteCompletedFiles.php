<?php

namespace App\Console\Commands;

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

        $files = File::where('status', 'completed')
            ->where('updated_at', '<=', $threshold)
            ->get();

        foreach ($files as $file) {
            $this->info("Deleting physical files for File ID {$file->id}...");
            $file->deletePhysicalFiles();
        }

        $this->info(count($files) . " physical files deleted.");
    }
}

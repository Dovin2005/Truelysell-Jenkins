<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ClearUploads extends Command
{
    protected $signature = 'storage:clear';
    protected $description = 'Delete uploaded files in storage/app/public and storage/app/private';

    public function handle()
    {
        $directories = [
            storage_path('app/public'),
            storage_path('app/private'),
        ];

        foreach ($directories as $directory) {
            if (File::exists($directory)) {
                $files = File::allFiles($directory);
                $subDirs = File::directories($directory);

                // Delete files except .gitignore
                foreach ($files as $file) {
                    if ($file->getFilename() !== '.gitignore') {
                        File::delete($file->getPathname());
                    }
                }

                foreach ($subDirs as $dir) {
                    File::deleteDirectory($dir);
                }

                $this->info("✅ Cleared uploads in: {$directory}");
            } else {
                $this->warn("⚠️ Directory does not exist: {$directory}");
            }
        }
    }
}

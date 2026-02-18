<?php

namespace Modules\Installer\app\Traits;

use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Modules\Installer\app\Models\Configuration;

trait InstallerMethods {
    private function checkMinimumRequirements(): array {
        $checks = [
            // Base requirements
            'php_version'         => [
                'check'   => PHP_VERSION_ID >= 80100,
                'message' => 'PHP version 8.1.0 or higher is required. Current version: ' . PHP_VERSION,
                'url'     => 'https://www.php.net/releases/8.1/en.php',
            ],

            'extension_bcmath'    => [
                'check'   => extension_loaded('bcmath'),
                'message' => 'The "bcmath" extension is required.',
                'url'     => 'https://www.php.net/manual/en/book.bc.php',
            ],

            'extension_ctype'     => [
                'check'   => extension_loaded('ctype'),
                'message' => 'The "ctype" extension is required.',
                'url'     => 'https://www.php.net/manual/en/book.ctype.php',
            ],

            'extension_json'      => [
                'check'   => extension_loaded('json'),
                'message' => 'The "json" extension is required.',
                'url'     => 'https://www.php.net/manual/en/book.json.php',
            ],

            'extension_mbstring'  => [
                'check'   => extension_loaded('mbstring'),
                'message' => 'The "mbstring" extension is required.',
                'url'     => 'https://www.php.net/manual/en/book.mbstring.php',
            ],

            'extension_openssl'   => [
                'check'   => extension_loaded('openssl'),
                'message' => 'The "openssl" extension is required.',
                'url'     => 'https://www.php.net/manual/en/book.openssl.php',
            ],

            'extension_pdo_mysql' => [
                'check'   => extension_loaded('pdo_mysql'),
                'message' => 'The "pdo_mysql" extension is required for MySQL database access.',
                'url'     => 'https://www.php.net/manual/en/ref.pdo-mysql.php',
            ],

            'extension_tokenizer' => [
                'check'   => extension_loaded('tokenizer'),
                'message' => 'The "tokenizer" extension is required.',
                'url'     => 'https://www.php.net/manual/en/book.tokenizer.php',
            ],

            'extension_xml'       => [
                'check'   => extension_loaded('xml'),
                'message' => 'The "xml" extension is required.',
                'url'     => 'https://www.php.net/manual/en/book.simplexml.php',
            ],
            'extension_zip'       => [
                'check'   => extension_loaded('zip'),
                'message' => 'The "zip" extension is required.',
                'url'     => 'https://www.php.net/manual/en/book.zip.php',
            ],

            'extension_php_intl'  => [
                'check'   => extension_loaded('intl'),
                'message' => 'The "intl" extension is recommended for localization features.',
                'url'     => 'https://www.php.net/manual/en/book.intl.php',
            ],

            // File and directory permissions
            'env_writable'        => [
                'check'   => File::isWritable(base_path('.env')),
                'message' => 'The ".env" file must be writable.',
            ],

            'storage_writable'    => [
                'check'   => File::isWritable(storage_path()) && File::isWritable(storage_path('logs')),
                'message' => 'The "storage" and "storage/logs" directories must be writable.',
            ],
        ];

        $failedChecks = [];
        foreach ($checks as $name => $check) {
            if (!$check['check']) {
                $failedChecks[$name] = [
                    'message' => $check['message'],
                    'url'     => isset($check['url']) ? $check['url'] : null,
                ];
            }
        }

        $success = empty($failedChecks);

        return [$checks, $success, $failedChecks];
    }

    private function requirementsCompleteStatus() {
        $success = $this->checkMinimumRequirements();

        return $success[1];
    }

    private function createDatabaseConnection($details)
    {
        try {
            // Get the default connection name
            $defaultConnectionName = config('database.default');
            $connection = config("database.connections.$defaultConnectionName");

            // Store original database name if needed
            $originalDatabase = $connection['database'] ?? null;

            // Update connection details with provided credentials
            $connection['host'] = $details['host'];
            $connection['port'] = $details['port'];
            $connection['username'] = $details['user'];
            $connection['password'] = $details['password'];

            // Temporarily set to 'information_schema' to check database existence
            $connection['database'] = 'information_schema';

            // Update the configuration
            Config::set("database.connections.$defaultConnectionName", $connection);

            // Purge and reconnect to apply the new configuration
            DB::purge($defaultConnectionName);
            DB::reconnect($defaultConnectionName);

            // Check if the target database exists
            $databaseExists = DB::connection($defaultConnectionName)
                ->select('SELECT SCHEMA_NAME FROM SCHEMATA WHERE SCHEMA_NAME = ?', [$details['database']]);

            if (empty($databaseExists)) {
                return 'not-found';
            }

            // Now set the connection to the target database
            $connection['database'] = $details['database'];
            Config::set("database.connections.$defaultConnectionName", $connection);
            DB::purge($defaultConnectionName);
            DB::reconnect($defaultConnectionName);

            // Check if the target database has existing tables
            $tables = DB::connection($defaultConnectionName)->select('SHOW TABLES');
            if (count($tables) > 0) {
                if (!empty($details['reset_database']) && $details['reset_database'] == 'on') {
                    // Drop all existing tables if reset is requested
                    foreach ($tables as $table) {
                        $tableName = array_values(get_object_vars($table))[0];
                        Schema::drop($tableName);
                    }
                    return true;
                }
                return 'table-exist';
            }

            return true;

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return 'Database connection failed! It looks like you have entered wrong database credentials (host, port, database, user, or password).';
        }
    }


    /**
     * Imports the database from a given SQL file.
     *
     * @param string $database_path
     * @return mixed
     */
    private function importDatabase($database_path)
    {
        // dd($database_path);
        if (File::exists($database_path)) {
            // dd($database_path);
            try {
                DB::unprepared(File::get($database_path));
                Log::info("Database imported successfully from '{$database_path}'.");
                return true;
            } catch (\Exception $e) {
                Log::error('Migration failed: ' . $e->getMessage());
                return 'Migration failed! Something went wrong';
            }
        } else {
            Log::error("Database import failed: File '{$database_path}' does not exist.");
            return 'Something went wrong';
        }
    }

    private function changeEnvDatabaseConfig($config) {
        $envContent = File::get(base_path('.env'));
        $lineBreak = "\n";
        $envContent = preg_replace([
            '/DB_HOST=(.*)\s/',
            '/DB_PORT=(.*)\s/',
            '/DB_DATABASE=(.*)\s/',
            '/DB_USERNAME=(.*)\s/',
            '/DB_PASSWORD=(.*)\s/',
        ], [
            'DB_HOST=' . $config['host'] . $lineBreak,
            'DB_PORT=' . $config['port'] . $lineBreak,
            'DB_DATABASE=' . $config['database'] . $lineBreak,
            'DB_USERNAME=' . $config['user'] . $lineBreak,
            'DB_PASSWORD=' . $config['password'] . $lineBreak,
        ], $envContent);

        if ($envContent !== null) {
            File::put(base_path('.env'), $envContent);
        }
    }

    private function completedSetup($type) {
        Configuration::updateCompeteStatus(1);
        Session::flush();
        Artisan::call('cache:clear');
        if ($type == 'admin') {
            return redirect()->route('storage-linkadmin');
        } else {
            return redirect()->route('storage-link');
        }
    }

    private function removeDummyFiles() {
        // delete files
        $this->deleteFolderAndFiles(public_path('uploads/custom-images'));
        $this->deleteFolderAndFiles(public_path('uploads/forum-images'));
        $this->deleteFolderAndFiles(public_path('uploads/store'));
    }

    private function deleteFolderAndFiles($directory) {
        // Check if the directory exists
        if (File::exists($directory)) {
            // Delete the directory and its contents
            File::deleteDirectory($directory);
            // Optional: recreate the empty directory if needed
            File::makeDirectory($directory);
            File::put($directory . '/.gitkeep', '');
        }
    }
}

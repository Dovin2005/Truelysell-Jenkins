<?php

namespace App\Repositories\Eloquent;

use App\Models\AddonModule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Repositories\Contracts\AddonRepositoryInterface;

class AddonRepository implements AddonRepositoryInterface
{
    public function index(Request $request): array
    {
        try {
            $orderBy = $request->order_by ?? 'desc';

            $addonModules = AddonModule::orderBy('id', $orderBy)
                ->get(['id', 'slug', 'name', 'version', 'price', 'status']);

            $response = Http::withoutVerifying()->get('https://truelysell-laravel-addons.dreamstechnologies.com/addonmodules.json');

            if ($response->successful()) {
                $jsonData = $response->json(); // or ->body()
            } else {
                 throw new \Exception("Failed to fetch JSON from the URL.");
            }


            $jsonCollection = collect($jsonData);

            $data = $addonModules->map(function ($dbModule) use ($jsonCollection) {
                $jsonModule = $jsonCollection->firstWhere('module_name', $dbModule->name);
                return [
                    'id' => $dbModule->id,
                    'slug' => $dbModule->slug,
                    'name' => $dbModule->name,
                    'version' => $dbModule->version,
                    'price' => $dbModule->price,
                    'status' => $dbModule->status,
                    'module_image' => $jsonModule['module_image'] ?? null,
                    'purchase_link' => $jsonModule['purchase_link'] ?? null,
                    'new_version' => $jsonModule['new_version'] ?? 'yes',
                ];
            });

            return [
                'code' => 200,
                'message' => __('Addon modules retrieved successfully.'),
                'data' => $data,
            ];
        } catch (\Exception $e) {
            return [
                'code' => 500,
                'message' => __('An error occurred while retrieving Addon module.'),
                'error' => $e->getMessage(),
            ];
        }
    }

    public function changeAddonStatus(Request $request): array
    {
        $id = $request->id;
        $status = $request->status;

        try {
            AddonModule::where('id', $id)->update([
                'status' => $status
            ]);

            Cache::forget('addonModules');

            return [
                'code' => 200,
                'message' => __('Addon status updated successfully.')
            ];
        } catch (\Exception $e) {
            return [
                'code' => 500,
                'message' => __('Addon status updated successfully.'),
            ];
        }
    }

    public function listNewAddonModules(Request $request): array
    {
        try {
            $jsonUrl = 'https://truelysell-laravel-addons.dreamstechnologies.com/addonmodules.json';
            $jsonData = @file_get_contents($jsonUrl);

            if ($jsonData === false) {
                throw new \Exception("Failed to fetch JSON from the URL.");
            }

            $jsonCollection = collect(json_decode($jsonData, true));

            $addonModules = AddonModule::get(['id', 'name', 'slug', 'version', 'price', 'status']);

            $modules = $jsonCollection->map(function ($jsonModule) use ($addonModules) {
                $dbModule = $addonModules->firstWhere('name', $jsonModule['module_name']);

                return [
                    'module_name' => $jsonModule['module_name'],
                    'module_image' => $jsonModule['module_image'],
                    'module_version' => $jsonModule['module_version'],
                    'module_price' => $jsonModule['module_price'],
                    'status' => $dbModule ? 1 : 0,
                    'git_link' => $jsonModule['git_link'] ?? '',
                    'purchase_link' => $jsonModule['purchase_link'] ?? '',
                    'new_version' => $jsonModule['new_version'] ?? 'yes',
                ];
            });

            return [
                'code' => 200,
                'message' => __('New Addon modules retrieved successfully.'),
                'data' => $modules,
            ];
        } catch (\Exception $e) {
            return [
                'code' => 500,
                'message' => __('An error occurred while retrieving New Addon modules.'),
                'error' => $e->getMessage()
            ];
        }
    }

    public function purchaseModule(Request $request): array
    {
        try {
            $verificationUrl = 'https://envato.dreamstechnologies.com/verify/verify.php';

            $request->validate([
                'purchase_code' => 'required|string',
                'module_name' => 'required|string',
                'module_version' => 'required|string',
                'module_price' => 'required|numeric',
                'git_link' => 'nullable',
            ]);

            $response = Http::asForm()->post($verificationUrl, [
                'purchase_code' => $request->purchase_code,
            ]);
            if (!$response->successful()) {
                return [
                    'code' => 400,
                    'message' => __('Failed to verify the purchase code. API returned an error.'),
                    'error' => $response->body(),
                ];
            }

            $verificationData = $response->json();
            Log::info('Verification API Response:', $verificationData);

            if (empty($verificationData) || !isset($verificationData['status']) || $verificationData['status'] !== true) {
                return [
                    'code' => 400,
                    'message' => __('Invalid purchase code. Please enter a valid purchase code.'),
                    'error' => $verificationData['html'] ?? 'Verification failed.',
                ];
            }


            $data = [
                'name' => $request->module_name,
                'version' => $request->module_version,
                'price' => $request->module_price,
                'status' => 1,
                'slug' => Str::slug(Str::plural($request->module_name))
            ];

            Log::info('Module Data:', $data);

            $moduleName = $request->module_name;
            $modulesPath = base_path("Modules/{$moduleName}");
            $moduleNameLower = strtolower($moduleName);


            if (!file_exists($modulesPath)) {
                return [
                    'code' => 500,
                    'message' => __('Please Insert the Modules File.'),

                ];
            }

            $this->copyJsFiles($moduleName, 'admin', public_path('assets/js/'));
            $this->copyJsFiles($moduleName, 'provider', public_path('front/js/'));

            $this->executeSqlFiles($moduleName);

            $this->updateModuleStatus($moduleName);

            $this->updateModulesConfig($moduleName, $moduleNameLower);

            AddonModule::create($data);

            return [
                'code' => 200,
                'message' => __('Module activated successfully.')
            ];
        } catch (\Exception $e) {
            Log::error('Module activation failed: ' . $e->getMessage());

            return [
                'code' => 500,
                'message' => __('An error occurred while activating modules.'),
                'error' => $e->getMessage()
            ];
        }
    }


    public function updateModule(Request $request): string
    {
        $moduleName = $request->module ?? '';
        $moduleName = ucfirst($moduleName);
        $modulesPath = base_path("Modules/{$moduleName}");
        $moduleNameLower = strtolower($moduleName);

        if (file_exists($modulesPath)) {
            $command = "cd {$modulesPath} && git reset --hard && git pull origin main 2>&1";
            $output = shell_exec($command);

            $this->copyJsFiles($moduleName, 'admin', public_path('assets/js/'));
            $this->copyJsFiles($moduleName, 'provider', public_path('front/js/'));

            $this->executeSqlFiles($moduleName);

            return 'Module updated successfully.';
        } else {
            abort(404, 'Module not found.');
        }
    }

    /**
     * Copy JavaScript files from module to public directory
     */
    private function copyJsFiles($moduleName, $type, $destinationDir)
    {
        $sourceDir = base_path("Modules/{$moduleName}/js/{$type}/");
        if (File::exists($sourceDir)) {
            $files = File::glob($sourceDir . '*.js');

            if (!empty($files)) {
                foreach ($files as $file) {
                    $fileName = basename($file);
                    $destinationPath = $destinationDir . '/' . $fileName;

                    if (!File::exists($destinationPath) || File::lastModified($file) > File::lastModified($destinationPath)) {
                        File::copy($file, $destinationPath);
                    }
                }
            }
        }
    }

    /**
     * Execute SQL files in the module
     */
    private function executeSqlFiles($moduleName)
    {
        $sqlPath = base_path("Modules/{$moduleName}/sql");
        if (File::exists($sqlPath)) {
            $sqlFiles = File::files($sqlPath);

            foreach ($sqlFiles as $file) {
                if ($file->getExtension() == 'sql') {
                    $sql = File::get($file);

                    if (preg_match('/CREATE TABLE\s+`?(\w+)`?/i', $sql, $matches)) {
                        $tableName = $matches[1];
                        $tableExists = DB::select("SHOW TABLES LIKE '{$tableName}'");

                        if ($tableExists) {
                            DB::statement("DROP TABLE IF EXISTS `{$tableName}`");
                        }

                        $sql = preg_replace('/CREATE TABLE\s+`?(\w+)`?/i', 'CREATE TABLE IF NOT EXISTS `$1`', $sql);
                    }

                    DB::unprepared($sql);
                }
            }
        }
    }

    /**
     * Update module status in modules_statuses.json
     */
    private function updateModuleStatus($moduleName)
    {
        $moduleStatusPath = base_path('modules_statuses.json');
        if (!File::exists($moduleStatusPath)) {
            File::put($moduleStatusPath, json_encode([], JSON_PRETTY_PRINT));
        }
        $modules = json_decode(File::get($moduleStatusPath), true);
        $modules[$moduleName] = true;
        File::put($moduleStatusPath, json_encode($modules, JSON_PRETTY_PRINT));
    }

    /**
     * Update modules.php configuration
     */
    private function updateModulesConfig($moduleName, $moduleNameLower)
    {
        $moduleClass = "Modules\\" . ucfirst($moduleName) . "\\Providers\\" . ucfirst($moduleName) . "ServiceProvider::class";
        $configPath = config_path('modules.php');
        if (File::exists($configPath)) {
            $configContent = File::get($configPath);

            if (strpos($configContent, "'$moduleNameLower' => [") === false) {
                $newModuleEntry = "\n        '$moduleNameLower' => [\n            'active' => true,\n            'providers' => [\n                $moduleClass,\n            ],\n        ],";
                $newConfigContent = preg_replace('/(\'modules\'\s*=>\s*\[)/s', "$1$newModuleEntry", $configContent, 1);
                File::put($configPath, $newConfigContent);
            }
        }
    }
}

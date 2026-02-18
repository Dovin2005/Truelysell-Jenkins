<?php

namespace Modules\Installer\app\Http\Controllers;

use Closure;
use Exception;
use App\Models\Administrator;
use App\Enums\UserStatus;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Modules\GlobalSetting\Entities\GlobalSetting;
use Modules\Installer\app\Enums\InstallerInfo;
use Modules\Installer\app\Models\Configuration;
use Modules\Installer\app\Traits\InstallerMethods;
use App\Models\User;
use App\Models\UserDetail;

class InstallerController extends Controller
{
    use InstallerMethods;

    public function __construct()
    {
        set_time_limit(8000000);
        $this->middleware(function (Request $request, Closure $next) {
            $data = purchaseVerificationHashed(InstallerInfo::getLicenseFilePath());
            if (isset($data) && isset($data['success']) && $data['success']) {
                return $next($request);
            } else {
                if (strtolower(config('app.app_mode')) == 'demo') {
                    return $next($request);
                }
            }

            return redirect()->route('setup.verify')->withInput()->withErrors(['errors' => isset($data) && isset($data['success']) && $data['message'] ? $data['message'] : 'License key not found']);
        });
    }

    public function requirements()
    {

        [$checks, $success, $failedChecks] = $this->checkMinimumRequirements();
        session()->put('step-1-complete', true);
        session()->put('step-2-complete', true);
        return view('installer::requirements', compact('checks', 'success', 'failedChecks'));
        if ($step = Configuration::stepExists() && $success) {
            if ($step == 5) {
                return redirect()->route('setup.complete');
            }
        }


    }

    public function database()
    {
        session()->put('step-1-complete', true);
        session()->put('step-2-complete', true);
        if ($this->requirementsCompleteStatus()) {
            session()->put('requirements-complete', true);

            return view('installer::database', ['isLocalHost' => InstallerInfo::isRemoteLocal()]);

            if (Configuration::stepExists()) {
                return redirect()->route('setup.account');
            }


        }

        return redirect()->route('setup.requirements')->withInput()->withErrors(['errors' => 'Your server does not meet the minimum requirements.']);
    }

    public function databaseSubmit(Request $request)
    {
        if (!$this->requirementsCompleteStatus()) {
            return redirect()->route('setup.requirements')->withInput()->withErrors(['errors' => 'Your server does not meet the minimum requirements.']);
        }

        try {
            $request->validate([
                'host' => 'required|ip',
                'port' => 'required|integer',
                'database' => 'required',
                'user' => 'required',
            ]);

            if (!InstallerInfo::isRemoteLocal()) {
                $request->validate([
                    'password' => 'required',
                ]);
            }

            $databaseCreate = $this->createDatabaseConnection($request->all());

            if ($databaseCreate !== true) {
                if ($databaseCreate == 'not-found') {
                    return response()->json(['create_database' => true, 'message' => 'Database not found! Please create the database first.'], 200);
                } elseif ($databaseCreate == 'table-exist') {
                    return response()->json(['reset_database' => true, 'message' => 'This database has tables already. Please create a new database or reset existing tables first to continue'], 200);
                } else {
                    return response()->json(['success' => false, 'message' => $databaseCreate], 200);
                }
            }

            $deleteDummyData = false;
            if ($request->has('fresh_install') && $request->filled('fresh_install') && $request->fresh_install == 'on') {
                $deleteDummyData = true;
                Cache::put('fresh_install', true, now()->addMinutes(60));
                $migration = $this->importDatabase(InstallerInfo::getFreshDatabaseFilePath());
            } else {
                $migration = $this->importDatabase(InstallerInfo::getDummyDatabaseFilePath());
            }

            if ($migration !== true) {
                return response()->json(['success' => false, 'message' => $migration], 200);
            }

            $this->changeEnvDatabaseConfig($request->except('reset_database'));

            if ($migration == true && $deleteDummyData) {
                $this->removeDummyFiles();
            }

            Cache::forget('fresh_install');

            session()->put('step-3-complete', true);
            Configuration::updateStep(1);

            return response()->json(['success' => true, 'message' => 'Successfully setup the database'], 200);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Database connection failed! Look like you have entered wrong database credentials (host, port, database, user or password).'], 200);
        }
    }

    protected function updateEnv(array $data)
    {
        foreach ($data as $key => $value) {
            file_put_contents(app()->environmentFilePath(), preg_replace(
                "/^{$key}=.*/m",
                "{$key}={$value}",
                file_get_contents(app()->environmentFilePath())
            ));
        }
    }

    public function account()
    {
        session()->put('step-1-complete', true);
        session()->put('step-2-complete', true);
        session()->put('step-3-complete', true);
        $step = Configuration::stepExists();
        if ($step >= 1 && $step < 5 && $this->requirementsCompleteStatus()) {
            $admin = $step >= 2 ? User::select('name', 'email')->first() : null;
            return view('installer::account', compact('admin'));
        }
        if ($step == 5 || !$this->requirementsCompleteStatus()) {
            return redirect()->route('setup.requirements');
        }
        return redirect()->route('setup.database');
    }

    public function accountSubmit(Request $request)
    {
        try {
            // Validate the incoming request
            $request->validate([
                'name' => 'required|string',
                'email' => 'required|email',
                'password' => 'required|same:confirm_password',
            ]);

            // Create or update the admin record
            $admin = User::updateOrCreate(
                ['email' => $request->email], // Find by email to update or create
                [
                    'name' => $request->name,
                    'password' => Hash::make($request->password),
                    'user_type' => 1,
                    'role_id' => 1,
                ]
            );

            UserDetail::updateOrCreate(
                ['user_id' => $admin->id],

            );

            // Update the setup step
            Configuration::updateStep(2);
            session()->put('step-4-complete', true);

            // Return success response
            return response()->json(['success' => true, 'message' => 'Admin Account Successfully Created'], 200);
        } catch (\Exception $e) {
            // Log the specific error message and stack trace
            Log::error($e->getMessage(), ['trace' => $e->getTraceAsString()]);

            // Return a detailed error response for debugging
            return response()->json(['success' => false, 'message' => 'Failed to Create Admin Account', 'error' => $e->getMessage()], 200);
        }
    }


    public function configuration()
    {
        session()->put('step-1-complete', true);
        session()->put('step-2-complete', true);
        session()->put('step-3-complete', true);
        session()->put('step-4-complete', true);
        $step = Configuration::stepExists();
        if ($step == 5 || !$this->requirementsCompleteStatus()) {
            return redirect()->route('setup.requirements');
        }
        if ($step < 2) {
            return redirect()->route('setup.account');
        }
        $app_name = $step >= 3 ? GlobalSetting::where('key', 'app_name')->first()->value : null;
        return view('installer::config', compact('app_name'));
    }

    public function configurationSubmit(Request $request)
    {
        try {
            $request->validate([
                'config_app_name' => 'required|string',
            ]);

            Configuration::updateStep(3);

            GlobalSetting::where('key', 'app_name')->update(['value' => $request->config_app_name]);

            if (Cache::has('last_updated_at')) {
                GlobalSetting::where('key', 'last_update_date')->update(['value' => Cache::get('last_updated_at')]);
                Cache::forget('last_updated_at');
            }

            Cache::forget('setting');
            Configuration::updateStep(4);
            session()->put('step-5-complete', true);
            session()->put('step-6-complete', true);

            return response()->json(['success' => true, 'message' => 'Configuration Successfully Saved'], 200);
        } catch (Exception $e) {
            Log::error($e);

            return response()->json(['success' => false, 'message' => 'Configuration Failed'], 200);
        }
    }
    public function smtp()
    {
        $step = Configuration::stepExists();

        if ($step == 4 || !$this->requirementsCompleteStatus()) {
            return redirect()->route('setup.complete');
        }
        if ($step < 3) {
            return redirect()->route('setup.configuration');
        }
        $email = null;
        $setting_info = Cache::get('setting');
        if ($step >= 4 && ($setting_info->mail_username != 'mail_username' && $setting_info->mail_password != 'mail_password')) {
            $email = [];
            $email['mail_host'] = $setting_info->mail_host;
            $email['email'] = $setting_info->mail_sender_email;
            $email['smtp_username'] = $setting_info->mail_username;
            $email['smtp_password'] = $setting_info->mail_password;
            $email['mail_port'] = $setting_info->mail_port;
            $email['mail_encryption'] = $setting_info->mail_encryption;
            $email['mail_sender_name'] = $setting_info->mail_sender_name;

            $email = (object) $email;
        }
        return view('installer::smtp', compact('email'));
    }
    public function smtpSetup(Request $request)
    {
        try {
            $rules = [
                'mail_host' => 'required',
                'email' => 'required',
                'smtp_username' => 'required',
                'smtp_password' => 'required',
                'mail_port' => 'required',
                'mail_encryption' => 'required',
                'mail_sender_name' => 'required',
            ];
            $customMessages = [
                'mail_host.required' => 'Mail host is required',
                'email.required' => 'Email is required',
                'smtp_username.required' => 'Smtp username is required',
                'smtp_password.unique' => 'Smtp password is required',
                'mail_port.required' => 'Mail port is required',
                'mail_encryption.required' => 'Mail encryption is required',
                'mail_sender_name.required' => 'Mail Sender Name is required',
            ];
            $this->validate($request, $rules, $customMessages);

            Setting::where('key', 'mail_host')->update(['value' => $request->mail_host]);
            Setting::where('key', 'mail_sender_email')->update(['value' => $request->email]);
            Setting::where('key', 'mail_username')->update(['value' => $request->smtp_username]);
            Setting::where('key', 'mail_password')->update(['value' => $request->smtp_password]);
            Setting::where('key', 'mail_port')->update(['value' => $request->mail_port]);
            Setting::where('key', 'mail_encryption')->update(['value' => $request->mail_encryption]);
            Setting::where('key', 'mail_sender_name')->update(['value' => $request->mail_sender_name]);

            Configuration::updateStep(4);

            session()->put('step-6-complete', true);

            Cache::forget('setting');

            return response()->json(['success' => true, 'message' => 'Successfully setup mail SMTP'], 200);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to Setup SMTP'], 200);
        }
    }

    public function smtpSkip()
    {
        Configuration::updateStep(4);
        session()->put('step-6-complete', true);
        return redirect()->route('setup.complete');
    }

    public function setupComplete()
    {
        session()->put('step-1-complete', true);
        session()->put('step-2-complete', true);
        session()->put('step-3-complete', true);
        session()->put('step-4-complete', true);
        session()->put('step-5-complete', true);
        session()->put('step-6-complete', true);
        session()->put('step-7-complete', true);

        if (Configuration::setupStepCheck(4) && $this->requirementsCompleteStatus()) {
            $envContent = File::get(base_path('.env'));
            $envContent = preg_replace(['/APP_ENV=(.*)\s/', '/APP_DEBUG=(.*)\s/',], ['APP_ENV=' . 'production' . "\n", 'APP_DEBUG=' . 'false' . "\n",], $envContent);
            if ($envContent !== null) {
                File::put(base_path('.env'), $envContent);
            }

            return view('installer::complete');
        }
        if (Configuration::setupStepCheck(5) && $this->requirementsCompleteStatus()) {
            return $this->completedSetup('home');
        }

        if (Configuration::stepExists() < 4) {
            return redirect()->route('setup.smtp');
        }

        return redirect()->back()->withInput()->withErrors(['errors' => 'Setup Is Incomplete hh']);
    }

    public function launchWebsite($type)
    {
        $result = $this->completedSetup($type);
        $filePath = base_path('modules_statuses.json');

        if (file_exists($filePath)) {
            $fileContent = file_get_contents($filePath);
            $statuses = json_decode($fileContent, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $statuses['Installer'] = false;
                $updatedContent = json_encode($statuses, JSON_PRETTY_PRINT);
                file_put_contents($filePath, $updatedContent);
            }
        }

        return $result;
    }
}

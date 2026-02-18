<?php
namespace Modules\Communication\app\Helpers;
use Illuminate\Support\Facades\Config;
use Modules\Communication\app\Models\CommunicationSettings;

class MailConfigurator
{
     /**
     * Configure mail settings based on communication settings.
     * @property string $key
     * * @property mixed $value
     * @return void
     */
    public static function configureMail(): void
    {
        $settingsdata = CommunicationSettings::where('settings_type', 1);
        $settings=$settingsdata->where(function($query) {
            $query->where(function($query) {
                $query->where('key', 'phpmail_status')
                      ->where('value', 1);
            })->orWhere(function($query) {
                $query->where('key', 'smtp_status')
                      ->where('value', 1);
            })->orWhere(function($query) {
                $query->where('key', 'sendgrid_status')
                      ->where('value', 1);
            });
        })->get();

        if (count($settings)>0) {
            if ($settings[0]->key=='phpmail_status' && $settings[0]->value==1) {
                $phpmail=CommunicationSettings::select('value')->where('settings_type', 1)->where('type','phpmail')->where('key','phpmail_from_email')->first();
                if(isset($phpmail)){
                    Config::set('mail.from.address', $phpmail->value);
                    $phpusername=CommunicationSettings::select('value')->where('settings_type', 1)->where('type','phpmail')->where('key','phpmail_from_name')->first();
                    Config::set('mail.from.name', $phpusername->value);
                    Config::set('mail.default', 'mail');
                    Config::set('mail.mailers.mail', [
                        'transport' => 'mail',
                    ]);
                }
            } else  if ($settings[0]->key=='smtp_status' && $settings[0]->value==1) {
                $getmail=CommunicationSettings::select('value')->where('settings_type', 1)->where('type','smtp')->where('key','smtp_from_email')->first();
                if(isset($getmail)){
                    $getpassword=CommunicationSettings::select('value')->where('settings_type', 1)->where('type','smtp')->where('key','smtp_password')->first();
                    Config::set('mail.from.address',  $getmail->value);
                    $getusername=CommunicationSettings::select('value')->where('settings_type', 1)->where('type','smtp')->where('key','smtp_from_name')->first();
                    $gethost=CommunicationSettings::select('value')->where('settings_type', 1)->where('type','smtp')->where('key','host')->first();
                    $getport=CommunicationSettings::select('value')->where('settings_type', 1)->where('type','smtp')->where('key','port')->first();

                    Config::set('mail.from.name', $getusername->value ?? "");
                    Config::set('mail.default', 'smtp');
                    Config::set('mail.mailers.smtp', [
                        'transport' => 'smtp',
                        'host' => $gethost->value ?? "",
                        'port' => $getport->value ?? 587,
                        'encryption' => 'tls',
                        'username' => $getmail->value ?? "",
                        'password' => $getpassword->value ?? "",
                    ]);
                }
            } else  if ($settings[0]->key=='sendgrid_status' && $settings[0]->value==1) {
                $getmail=CommunicationSettings::select('value')->where('settings_type', 1)->where('type','sendgrid')->where('key','sendgrid_from_email')->first();
                if(isset($getmail)){
                    $getkey=CommunicationSettings::select('value')->where('settings_type', 1)->where('type','sendgrid')->where('key','sendgrid_key')->first();
                    $getFromName = getCommonSettingData(['app_name']);

                    Config::set('mail.from.address', $getmail->value ?? "");
                    Config::set('mail.from.name', $getFromName['app_name'] ?? "");
                    Config::set('mail.default', 'smtp');
                    Config::set('mail.mailers.smtp', [
                        'transport' => 'smtp',
                        'host' => 'smtp.sendgrid.net',
                        'port' => 587,
                        'encryption' => 'tls',
                        'username' =>'apikey',
                        'password' => $getkey->value ?? "",
                    ]);
                }
            }
        } else {
            throw new \Exception("No mail configuration found.");
        }
    }
}

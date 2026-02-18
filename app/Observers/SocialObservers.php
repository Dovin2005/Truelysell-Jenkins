<?php

namespace App\Observers;

use Modules\GeneralSetting\Models\SocialMediaShare;
use Illuminate\Support\Facades\Cache;

class SocialObserver
{
    public function created(SocialMediaShare $share)
    {
        Cache::forget("global_social_media_shares");
    }

    public function updated(SocialMediaShare $share)
    {
        Cache::forget("global_social_media_shares");
    }

    public function deleted(SocialMediaShare $share)
    {
        Cache::forget("global_social_media_shares");
    }
}
<?php 
namespace App\Helpers;
use Carbon\Carbon;

class TimeHelper
{
    public static function getRelativeTime($dateString)
    {
        $now = Carbon::now(); 
        $givenDate = Carbon::parse($dateString);
        $diffInSeconds = $now->diffInSeconds($givenDate, false); // Use false to retain negative values
    
        if ($diffInSeconds < 0) {
            $diffInSeconds = abs($diffInSeconds); // Work with absolute value for formatting
            if ($diffInSeconds < 60) {
                return "A few seconds ago";
            } elseif ($diffInSeconds < 3600) {
                $minutes = floor($diffInSeconds / 60);
                return "{$minutes} minute" . ($minutes > 1 ? 's' : '') . " ago";
            } elseif ($diffInSeconds < 86400) {
                $hours = floor($diffInSeconds / 3600);
                return "{$hours} hour" . ($hours > 1 ? 's' : '') . " ago";
            } elseif ($diffInSeconds < 2592000) {
                $days = floor($diffInSeconds / 86400);
                return "{$days} day" . ($days > 1 ? 's' : '') . " ago";
            } elseif ($diffInSeconds < 31536000) {
                $months = floor($diffInSeconds / 2592000);
                return "{$months} month" . ($months > 1 ? 's' : '') . " ago";
            } else {
                $years = floor($diffInSeconds / 31536000);
                return "{$years} year" . ($years > 1 ? 's' : '') . " ago";
            }
        } else {
            if ($diffInSeconds < 60) {
                return "Just now";
            } elseif ($diffInSeconds < 3600) {
                $minutes = floor($diffInSeconds / 60);
                return "{$minutes} minute" . ($minutes > 1 ? 's' : '') . " ago";
            } elseif ($diffInSeconds < 86400) {
                $hours = floor($diffInSeconds / 3600);
                return "{$hours} hour" . ($hours > 1 ? 's' : '') . " ago";
            } elseif ($diffInSeconds < 2592000) {
                $days = floor($diffInSeconds / 86400);
                return "{$days} day" . ($days > 1 ? 's' : '') . " ago";
            } elseif ($diffInSeconds < 31536000) {
                $months = floor($diffInSeconds / 2592000);
                return "{$months} month" . ($months > 1 ? 's' : '') . " ago";
            } else {
                $years = floor($diffInSeconds / 31536000);
                return "{$years} year" . ($years > 1 ? 's' : '') . " ago";
            }
        }
    }
    
}
?>
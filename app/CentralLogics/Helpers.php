<?php

namespace App\CentralLogics;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Helpers
{
    /**
     * Upload an image to the specified directory.
     *
     * @param string $dir Directory path where the image will be uploaded.
     * @param string $format File format of the image (e.g., jpg, png).
     * @param mixed $image The image file to be uploaded.
     * @return string|null The name of the uploaded image or null if no image provided.
     */
    public static function upload(string $dir, string $format, $image = null): ?string
    {
        if ($image !== null) {
            $imageName = strtotime(Carbon::now()) . uniqid() . '.' . $format;

            if (!Storage::disk('public_uploads')->exists($dir)) {
                Storage::disk('public_uploads')->makeDirectory($dir);
            }

            $fileContents = file_get_contents($image);

            if ($fileContents === false) {
                throw new \RuntimeException("Failed to read the file contents.");
            }

            Storage::disk('public_uploads')->put($dir . $imageName, $fileContents);

            return $imageName;
        }

        return null;
    }


    /**
     * Update an existing image by replacing it with a new one.
     *
     * @param string $dir Directory path where the image is stored.
     * @param string|null $old_image The name of the old image.
     * @param string $format File format of the new image.
     * @param mixed $image The new image file to be uploaded.
     * @return string|null The name of the updated image or the old image name if no new image is provided.
     */
    public static function update(string $dir, ?string $old_image, string $format, $image = null): ?string
    {
        if ($image === null) {
            return $old_image;
        }

        if ($old_image && Storage::disk('public_uploads')->exists($dir . $old_image)) {
            Storage::disk('public_uploads')->delete($dir . $old_image);
        }

        return self::upload($dir, $format, $image);
    }

    /**
     * Delete an image from the specified directory.
     *
     * @param string $dir Directory path where the image is stored.
     * @param string|null $old_image The name of the image to delete.
     * @return bool True if the deletion was successful or the file does not exist.
     */
    public static function delete(string $dir, ?string $old_image): bool
    {
        if ($old_image && Storage::disk('public_uploads')->exists($dir . $old_image)) {
            Storage::disk('public_uploads')->delete($dir . $old_image);
        }
        return true;
    }

    /**
     * Get the URL for sending a push notification to a specific user.
     *
     * @return string The API URL for specific user push notifications.
     */
    public static function specific_user_push_notification_url(): string
    {
        return config('app.api_url') . '/api/v1/send-specific-push-notification';
    }

    /**
     * Get the URL for sending push notifications to user segments.
     *
     * @return string The API URL for segment-based push notifications.
     */
    public static function segments_user_push_notification_url(): string
    {
        return config('app.api_url') . '/api/v1/send-segments-push-notification';
    }
}

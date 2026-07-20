<?php

namespace App\Support;

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

class ImageUploader
{
    /**
     * Optimize (downscale + WebP recompress) and store an uploaded image,
     * returning the value to persist in the `image` column.
     *
     * Optimization runs on BOTH drivers so a heavy upload is never stored at
     * full weight:
     * - 'local'      -> resized/encoded with Intervention (GD) and stored on the
     *                   public disk as "<folder>/<uuid>.webp"; Resources serve it
     *                   via asset('storage/...').
     * - 'cloudinary' -> uploaded with an incoming transformation (limit + quality)
     *                   and stored as WebP; returns the secure (https) URL.
     *
     * Resources already tell a full URL from a local path apart, so the frontend
     * always receives a usable URL regardless of the driver.
     *
     * @param  int|null  $maxSize  longest-side cap in px (defaults to config); never upscales
     * @param  int|null  $quality  WebP quality 0-100 (defaults to config)
     */
    public static function upload(UploadedFile $file, string $folder, ?int $maxSize = null, ?int $quality = null): string
    {
        $maxSize = $maxSize ?? (int) config('images.max_size', 1024);
        $quality = $quality ?? (int) config('images.quality', 80);

        if (config('images.driver') === 'cloudinary') {
            return Cloudinary::uploadApi()->upload(
                $file->getRealPath(),
                [
                    'folder' => $folder,
                    'format' => 'webp',
                    'transformation' => [
                        ['width' => $maxSize, 'height' => $maxSize, 'crop' => 'limit'],
                        ['quality' => $quality],
                    ],
                ]
            )['secure_url'];
        }

        $image = ImageManager::usingDriver(GdDriver::class)->decodePath($file->getRealPath());
        $image->scaleDown($maxSize, $maxSize);
        $encoded = (string) $image->encode(new WebpEncoder(quality: $quality));

        $path = $folder.'/'.Str::uuid()->toString().'.webp';
        Storage::disk('public')->put($path, $encoded);

        return $path;
    }
}

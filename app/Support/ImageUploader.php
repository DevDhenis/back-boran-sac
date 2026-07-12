<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ImageUploader
{
    /**
     * Sube una imagen y devuelve el valor a guardar en la columna `imagen`.
     *
     * Según config('images.driver'):
     * - 'cloudinary' -> URL segura (https) de Cloudinary.
     * - 'local'      -> ruta relativa en el disco público (ej. "products/abc.jpg"),
     *                   que los Resources sirven vía asset('storage/...').
     *
     * Los Resources ya distinguen entre URL completa y ruta local, así que el
     * frontend recibe siempre una URL utilizable sin importar el driver.
     */
    public static function upload(UploadedFile $file, string $folder): string
    {
        if (config('images.driver') === 'cloudinary') {
            return Cloudinary::uploadApi()->upload(
                $file->getRealPath(),
                ['folder' => $folder]
            )['secure_url'];
        }

        return $file->store($folder, 'public');
    }
}

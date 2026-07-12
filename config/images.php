<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Driver de subida de imágenes
    |--------------------------------------------------------------------------
    |
    | 'local'      -> guarda en el disco público (storage/app/public). Ideal para
    |                 desarrollo: no depende de Cloudinary y ves los cambios al instante.
    | 'cloudinary' -> sube a Cloudinary y guarda la URL segura. Se usa en producción
    |                 (Render), cuyo disco es efímero.
    |
    */

    'driver' => env('IMAGE_UPLOAD_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Optimización de imágenes
    |--------------------------------------------------------------------------
    |
    | Toda imagen subida se redimensiona (lado mayor <= max_size, sin ampliar)
    | y se recomprime a WebP con esta calidad, en ambos drivers. Los callers
    | pueden pasar un max_size distinto (p. ej. avatares más pequeños).
    |
    */

    'max_size' => (int) env('IMAGE_MAX_SIZE', 1024),

    'quality' => (int) env('IMAGE_QUALITY', 80),

];

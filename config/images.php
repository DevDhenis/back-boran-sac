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

];

<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\UpdateRequest;
use App\Http\Resources\ProfileResource;
use App\Support\ImageUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    /**
     * Devuelve el perfil del usuario autenticado (datos de cuenta + persona).
     */
    public function show(): JsonResponse
    {
        $user = auth()->user()->load('person.documentType');

        return response()->json([
            'success' => true,
            'message' => 'Perfil obtenido correctamente.',
            'data' => new ProfileResource($user),
        ]);
    }

    /**
     * Actualiza los datos personales esenciales del usuario autenticado.
     * Opera siempre sobre el usuario del token: nunca modifica rol, contraseña
     * ni el perfil de otro usuario.
     */
    public function update(UpdateRequest $request): JsonResponse
    {
        $user = auth()->user();

        DB::transaction(function () use ($request, $user) {
            $data = $request->validated();

            $user->load('person');

            if ($request->hasFile('image')) {
                // Guarda la image según el driver (local en dev, Cloudinary en prod).
                $data['image'] = ImageUploader::upload($request->file('image'), 'users');
            }

            $personData = collect($data)->only([
                'first_name',
                'last_name',
                'second_last_name',
                'address',
                'image',
                'document_number',
                'document_type_id',
            ])->toArray();

            $userData = collect($data)->only([
                'username',
                'email',
            ])->toArray();

            if (! empty($personData) && $user->person) {
                $user->person->update($personData);
            }

            if (! empty($userData)) {
                $user->update($userData);
            }
        });

        $user->load('person.documentType');

        return response()->json([
            'success' => true,
            'message' => 'Perfil actualizado correctamente.',
            'data' => new ProfileResource($user),
        ]);
    }
}

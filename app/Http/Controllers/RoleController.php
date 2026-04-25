<?php

namespace App\Http\Controllers;

use App\Http\Requests\Role\AssignUserRequest;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\SyncAccessRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Resources\PersonResource;
use App\Models\Access;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\JsonResponse;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        $roles = Role::all();

        return response()->json([
            'success' => true,
            'message' => 'Lista de roles obtenida correctamente.',
            'data' => $roles,
        ]);
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = Role::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Rol creado con éxito.',
            'data'    => [
                'id'          => $role->id,
                'nombre'      => $role->nombre,
                'descripcion' => $role->descripcion,
            ]
        ], 201);
    }

    public function show(Role $role): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Rol obtenido correctamente.',
            'data' => $role,
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        $role->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Rol actualizado con éxito.',
            'data' => $role,
        ]);
    }

    public function destroy(Role $role): JsonResponse
    {
        if ($role->users()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar este rol porque tiene usuarios relacionados.',
            ], 409);
        }

        $role->accesses()->detach();

        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Rol eliminado con éxito.',
        ]);
    }

    public function getAccesses(Role $role): JsonResponse
    {
        $roleAccessIds = $role->accesses()->pluck('accesses.id');

        return response()->json([
            'success' => true,
            'message' => 'Accesos del rol obtenidos  correctamente.',
            'data' => [
                'role' => $role,
                'assigned' => $roleAccessIds,
            ],
        ]);
    }

    public function syncAccesses(SyncAccessRequest $request, Role $role): JsonResponse
    {
        $role->accesses()->sync($request->validated()['access_ids']);

        return response()->json([
            'success' => true,
            'message' => 'Accesos asignados al rol correctamente.',
        ]);
    }

    public function assignUser(AssignUserRequest $request, Role $role)
    {
        $employee = Employee::findOrFail($request->employee_id);
        $personId = $employee->person_id;

        if ($employee->person->user) {
            return response()->json([
                'success' => false,
                'message' => 'Este empleado ya tiene un usuario asignado.',
            ], 409);
        }

        $user = User::create([
            'username'   => $request->username,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'role_id'    => $role->id,
            'person_id'  => $personId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Usuario creado y asignado al rol correctamente.',
            'data'    => $user,
        ], 201);
    }

    public function users(Role $role): JsonResponse
    {
        $users = $role->users()->with('person')->get();

        return response()->json([
            'success' => true,
            'message' => 'Usuarios vinculados al rol obtenidos correctamente.',
            'data'    => $users->map(function ($user) {
                return [
                    'id'       => $user->id,
                    'username' => $user->username,
                    'email'    => $user->email,
                    'role_id'  => $user->role_id,
                    'person'   => new PersonResource($user->person),
                ];
            }),
        ]);
    }
}

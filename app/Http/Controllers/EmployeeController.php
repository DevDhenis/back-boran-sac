<?php

namespace App\Http\Controllers;

use App\Http\Requests\Employee\StoreRequest;
use App\Http\Requests\Employee\UpdateRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use App\Models\Person;
use App\Support\ImageUploader;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\JsonResponse;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::with('person.documentType')->get();

        return response()->json([
            'success' => true,
            'message' => 'Lista de empleados obtenida correctamente.',
            'data'    => EmployeeResource::collection($employees)
        ]);
    }

    public function store(StoreRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($request, $data) {

            if ($request->hasFile('imagen')) {
                // Guarda la imagen según el driver (local en dev, Cloudinary en prod).
                $data['imagen'] = ImageUploader::upload($request->file('imagen'), 'empleados');
            }

            $person = Person::create($data);

            return Employee::create([
                'person_id'       => $person->id,
                'horario_laboral' => $data['horario_laboral'],
                'sueldo'          => $data['sueldo'],
                'estado_registro' => 'A',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Empleado registrado correctamente'
        ], 201);
    }

    public function show($id)
    {
        $employee = Employee::with('person.documentType')->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Empleado obtenido correctamente.',
            'data'    => new EmployeeResource($employee)
        ]);
    }

    public function update(UpdateRequest $request, Employee $employee): JsonResponse
    {
        DB::transaction(function () use ($request, $employee) {
            $data = $request->validated();

            $employee->load('person');

            if ($request->hasFile('imagen')) {
                // Reemplaza la imagen (local en dev, Cloudinary en prod).
                $data['imagen'] = ImageUploader::upload($request->file('imagen'), 'empleados');
            }

            $personData = collect($data)->only([
                'nombres',
                'apellido_paterno',
                'apellido_materno',
                'direccion',
                'imagen',
                'numero_documento',
                'document_type_id'
            ])->toArray();

            $employeeData = collect($data)->only([
                'horario_laboral',
                'sueldo',
                'estado_registro'
            ])->toArray();

            if (!empty($personData) && $employee->person) {
                $employee->person->update($personData);
            }

            if (!empty($employeeData)) {
                $employee->update($employeeData);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Empleado actualizado correctamente',
        ]);
    }

    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            $employee = Employee::findOrFail($id);
            $person = $employee->person;

            if ($person->user) {
                $person->user->update(['estado_registro' => 'I']);
            }

            $employee->delete();

            $person->delete();

            return response()->json([
                'success' => true,
                'message' => 'Empleado eliminado correctamente.'
            ]);
        });
    }


    public function suspend($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->update(['estado_registro' => 'I']);

        if ($employee->person && $employee->person->user) {
            $employee->person->user->update(['estado_registro' => 'I']);
        }

        return response()->json(['message' => 'Empleado suspendido correctamente']);
    }

    public function activate($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->update(['estado_registro' => 'A']);
        return response()->json(['message' => 'Empleado activado correctamente']);
    }
}

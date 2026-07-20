<?php

namespace App\Http\Controllers;

use App\Http\Requests\Employee\StoreRequest;
use App\Http\Requests\Employee\UpdateRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use App\Models\Person;
use App\Support\ImageUploader;
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
            'data' => EmployeeResource::collection($employees),
        ]);
    }

    public function store(StoreRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($request, $data) {

            if ($request->hasFile('image')) {
                // Guarda la image según el driver (local en dev, Cloudinary en prod).
                $data['image'] = ImageUploader::upload($request->file('image'), 'empleados');
            }

            $person = Person::create($data);

            return Employee::create([
                'person_id' => $person->id,
                'work_schedule' => $data['work_schedule'],
                'salary' => $data['salary'],
                'status' => 'A',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Empleado registrado correctamente',
        ], 201);
    }

    public function show($id)
    {
        $employee = Employee::with('person.documentType')->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Empleado obtenido correctamente.',
            'data' => new EmployeeResource($employee),
        ]);
    }

    public function update(UpdateRequest $request, Employee $employee): JsonResponse
    {
        DB::transaction(function () use ($request, $employee) {
            $data = $request->validated();

            $employee->load('person');

            if ($request->hasFile('image')) {
                // Reemplaza la image (local en dev, Cloudinary en prod).
                $data['image'] = ImageUploader::upload($request->file('image'), 'empleados');
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

            $employeeData = collect($data)->only([
                'work_schedule',
                'salary',
                'status',
            ])->toArray();

            if (! empty($personData) && $employee->person) {
                $employee->person->update($personData);
            }

            if (! empty($employeeData)) {
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
                $person->user->update(['status' => 'I']);
            }

            $employee->delete();

            $person->delete();

            return response()->json([
                'success' => true,
                'message' => 'Empleado eliminado correctamente.',
            ]);
        });
    }

    public function suspend($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->update(['status' => 'I']);

        if ($employee->person && $employee->person->user) {
            $employee->person->user->update(['status' => 'I']);
        }

        return response()->json(['message' => 'Empleado suspendido correctamente']);
    }

    public function activate($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->update(['status' => 'A']);

        return response()->json(['message' => 'Empleado activado correctamente']);
    }
}

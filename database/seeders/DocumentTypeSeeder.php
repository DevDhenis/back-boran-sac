<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            'DNI',
            'Carnet de extranjería',
            'Pasaporte',
            'RUC',
        ];

        foreach ($types as $name) {
            DocumentType::firstOrCreate(
                ['name' => $name],
                ['status' => 'A']
            );
        }
    }
}

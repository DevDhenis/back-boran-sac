<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DocumentType;

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
                ['nombre' => $name],
                ['estado_registro' => 'A']
            );
        }
    }
}
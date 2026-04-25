<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo_interno'       => 'required|string|max:50|unique:products,codigo_interno',
            'nombre'               => 'required|string|max:255',
            'descripcion'          => 'nullable|string|max:500',
            'stock'                => 'nullable|numeric|min:0',
            'cantidad_minima'      => 'nullable|numeric|min:0',
            'en_promocion'         => 'boolean',
            'pre_uni'              => 'required|numeric|min:0',
            'pre_uni_may'          => 'required|numeric|min:0',
            'can_min_may'          => 'required|numeric|min:0',
            'descuento'            => 'nullable|integer|min:0|max:100',
            'imagen'               => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'unit_id'              => 'required|exists:units,id',
            'product_category_id'  => 'required|exists:product_categories,id',
        ];
    }

    public function messages(): array
    {
        return [
            'imagen.image' => 'El archivo debe ser una imagen válida (JPG, JPEG, PNG)',
            'imagen.mimes' => 'Solo se permiten imágenes en formato JPG, JPEG o PNG. Formatos como WEBP no son compatibles',
            'imagen.max' => 'La imagen no debe pesar más de 2MB',
            'codigo_interno.required' => 'El código interno es obligatorio',
            'codigo_interno.unique' => 'Este código interno ya está registrado',
            'nombre.required' => 'El nombre del producto es obligatorio',
            'pre_uni.required' => 'El precio unitario es obligatorio',
            'pre_uni_may.required' => 'El precio por mayor es obligatorio',
            'can_min_may.required' => 'La cantidad mínima por mayor es obligatoria',
            'unit_id.required' => 'Debe seleccionar una unidad',
            'product_category_id.required' => 'Debe seleccionar una categoría',
        ];
    }

    public function attributes(): array
    {
        return [
            'codigo_interno' => 'código interno',
            'pre_uni' => 'precio unitario',
            'pre_uni_may' => 'precio por mayor',
            'can_min_may' => 'cantidad mínima por mayor',
            'unit_id' => 'unidad',
            'product_category_id' => 'categoría',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('en_promocion')) {
            $this->merge([
                'en_promocion' => filter_var($this->en_promocion, FILTER_VALIDATE_BOOLEAN)
            ]);
        }
    }
}

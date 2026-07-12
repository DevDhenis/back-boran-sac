<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product')->id ?? $this->route('product');

        return [
            'internal_code' => 'required|string|max:50|unique:products,internal_code,'.$productId,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'stock' => 'nullable|numeric|min:0',
            'minimum_quantity' => 'nullable|numeric|min:0',
            'on_promotion' => 'boolean',
            'unit_price' => 'required|numeric|min:0',
            'wholesale_unit_price' => 'required|numeric|min:0',
            'wholesale_min_quantity' => 'required|numeric|min:0',
            'discount' => 'nullable|integer|min:0|max:100',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'unit_id' => 'required|exists:units,id',
            'product_category_id' => 'required|exists:product_categories,id',
        ];
    }

    public function messages(): array
    {
        return [
            'image.image' => 'El archivo debe ser una image válida (JPG, JPEG, PNG)',
            'image.mimes' => 'Solo se permiten imágenes en formato JPG, JPEG o PNG. Formatos como WEBP no son compatibles',
            'image.max' => 'La image no debe pesar más de 2MB',
            'internal_code.required' => 'El código interno es obligatorio',
            'internal_code.unique' => 'Este código interno ya está registrado',
            'name.required' => 'El name del producto es obligatorio',
            'unit_price.required' => 'El precio unitario es obligatorio',
            'wholesale_unit_price.required' => 'El precio por mayor es obligatorio',
            'wholesale_min_quantity.required' => 'La quantity mínima por mayor es obligatoria',
            'unit_id.required' => 'Debe seleccionar una unidad',
            'product_category_id.required' => 'Debe seleccionar una categoría',
        ];
    }

    public function attributes(): array
    {
        return [
            'internal_code' => 'código interno',
            'unit_price' => 'precio unitario',
            'wholesale_unit_price' => 'precio por mayor',
            'wholesale_min_quantity' => 'quantity mínima por mayor',
            'unit_id' => 'unidad',
            'product_category_id' => 'categoría',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('on_promotion')) {
            $this->merge([
                'on_promotion' => filter_var($this->on_promotion, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}

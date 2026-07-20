<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class DashboardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'range' => 'sometimes|in:7d,30d,90d',
        ];
    }

    public function messages(): array
    {
        return [
            'range.in' => 'El rango debe ser 7d, 30d o 90d.',
        ];
    }

    /**
     * Number of days for the selected range (defaults to 30).
     */
    public function days(): int
    {
        return match ($this->query('range', '30d')) {
            '7d' => 7,
            '90d' => 90,
            default => 30,
        };
    }
}

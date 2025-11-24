<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

class PermissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $permissionId = request()->route('id');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('permissions', 'name')->ignore($permissionId),
                function ($attribute, $value, $fail) use ($permissionId) {
                    // Validar que el nombre no esté vacío después de trim
                    $name = trim($value);
                    if (empty($name)) {
                        $fail('Permission name cannot be empty or contain only whitespace');
                        return;
                    }

                    // Validar formato del nombre del permiso (solo letras, números, guiones, guiones bajos y espacios)
                    if (!preg_match('/^[a-zA-Z0-9_ -]+$/', $name)) {
                        $fail('Permission name can only contain letters, numbers, hyphens, underscores, and spaces');
                        return;
                    }

                    // Verificar duplicados ignorando mayúsculas/minúsculas
                    $query = Permission::whereRaw('LOWER(name) = ?', [strtolower($name)]);

                    // Si estamos actualizando, excluir el permiso actual
                    if ($permissionId) {
                        $query->where('id', '!=', $permissionId);
                    }

                    if ($query->exists()) {
                        $fail('A permission with this name already exists (case-insensitive match)');
                    }
                },
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Permission name is required',
            'name.string' => 'Permission name must be a string',
            'name.max' => 'Permission name cannot exceed 255 characters',
            'name.unique' => 'A permission with this name already exists',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim($this->input('name', '')),
        ]);
    }
}

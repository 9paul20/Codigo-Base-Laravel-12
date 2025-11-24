<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class RoleRequest extends FormRequest
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
        $roleId = request()->route('id');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($roleId),
                function ($attribute, $value, $fail) use ($roleId) {
                    // Validar que el nombre no esté vacío después de trim
                    $name = trim($value);
                    if (empty($name)) {
                        $fail('Role name cannot be empty or contain only whitespace');
                        return;
                    }

                    // Validar formato del nombre del rol (solo letras, números, guiones, guiones bajos y espacios)
                    if (!preg_match('/^[a-zA-Z0-9_ -]+$/', $name)) {
                        $fail('Role name can only contain letters, numbers, hyphens, underscores, and spaces');
                        return;
                    }

                    // Verificar duplicados ignorando mayúsculas/minúsculas
                    $query = Role::whereRaw('LOWER(name) = ?', [strtolower($name)]);

                    // Si estamos actualizando, excluir el rol actual
                    if ($roleId) {
                        $query->where('id', '!=', $roleId);
                    }

                    if ($query->exists()) {
                        $fail('A role with this name already exists (case-insensitive match)');
                    }
                },
            ],
            'guard_name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'permissions' => [
                'nullable',
                'array',
            ],
            'permissions.*' => [
                'integer',
                'exists:permissions,id',
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
            'name.required' => 'Role name is required',
            'name.string' => 'Role name must be a string',
            'name.max' => 'Role name cannot exceed 255 characters',
            'name.unique' => 'A role with this name already exists',
            'guard_name.string' => 'Guard name must be a string',
            'guard_name.max' => 'Guard name cannot exceed 255 characters',
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

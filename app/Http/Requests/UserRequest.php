<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $userId = $this->segment(count($this->segments()));

        return [
            'name' => 'required|string|max:255|unique:users,name,' . $userId,
            'email' => 'required|string|email|max:255|unique:users,email,' . $userId,
            'password' => $this->isMethod('post') ? 'required|string|min:8|confirmed' : 'sometimes|string|min:8|confirmed',
            'status_id' => 'sometimes|exists:statuses,id',
            'roles' => 'sometimes|array',
            'roles.*' => 'exists:roles,id',
            'permissions' => 'sometimes|array',
            'permissions.*' => 'exists:permissions,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'User name is required',
            'name.string' => 'User name must be a string',
            'name.max' => 'User name cannot exceed 255 characters',
            'name.unique' => 'A user with this name already exists',
            'email.required' => 'Email is required',
            'email.string' => 'Email must be a string',
            'email.email' => 'Email must be a valid email address',
            'email.max' => 'Email cannot exceed 255 characters',
            'email.unique' => 'A user with this email already exists',
            'password.required' => 'Password is required',
            'password.string' => 'Password must be a string',
            'password.min' => 'Password must be at least 8 characters',
            'password.confirmed' => 'Password confirmation does not match',
            'status_id.exists' => 'Selected status does not exist',
            'roles.array' => 'Roles must be an array',
            'roles.*.exists' => 'Selected role does not exist',
            'permissions.array' => 'Permissions must be an array',
            'permissions.*.exists' => 'Selected permission does not exist',
        ];
    }

    protected function failedValidation(Validator $validator): array
    {
        $name = $this->input('name');
        $email = $this->input('email');

        $response = [
            'severity' => 'error',
            'summary' => 'Validation Error',
            'detail' => 'Error in the validation of user data',
            'name' => $name,
            'email' => $email,
            'errors' => $validator->errors()
        ];

        throw new HttpResponseException(response()->json($response, 422));
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $name = trim($this->input('name', ''));

            // Validar que el nombre no esté vacío después de trim
            if (empty($name)) {
                $validator->errors()->add('name', 'User name cannot be empty or contain only whitespace');
                return;
            }

            // Verificar email duplicado ignorando mayúsculas/minúsculas
            $email = strtolower(trim($this->input('email', '')));
            $userId = $this->segment(count($this->segments()));
            $existingUser = User::whereRaw('LOWER(email) = ?', [$email])
                ->when($userId, function ($query) use ($userId) {
                    return $query->where('id', '!=', $userId);
                })
                ->first();

            // if ($existingUser) {
            //     $validator->errors()->add('email', 'A user with this email already exists (case-insensitive match)');
            // }

            // Validar cambio de status propio (solo en update)
            if ($this->isMethod('patch') && $this->has('status_id')) {
                $userId = $this->segment(count($this->segments()));
                if ($userId == Auth::id()) {
                    $validator->errors()->add('status_id', 'Cannot change your own status');
                }
            }

            // Validar permisos si se proporcionan
            if ($this->has('permissions') && is_array($this->permissions)) {
                $user = Auth::user();
                if ($user && !$this->canAssignPermissions($user, $this->permissions)) {
                    $validator->errors()->add('permissions', 'You do not have permission to assign some of the requested permissions');
                }
            }

            // Validar roles si se proporcionan (solo en update)
            if ($this->isMethod('patch') && $this->has('roles') && is_array($this->roles)) {
                if (empty($this->roles)) {
                    $validator->errors()->add('roles', 'User must have at least one role');
                }
            }
        });
    }

    /**
     * Check if user can assign specific permissions
     */
    private function canAssignPermissions(User $user, array $permissionIds): bool
    {
        // Super admin puede asignar cualquier permiso
        if ($user->hasRole('super admin')) {
            return true;
        }

        $adminOnlyPermissions = ['manage-users', 'manage-roles', 'manage-permissions', 'system-config'];

        foreach ($permissionIds as $permissionId) {
            $permission = \Spatie\Permission\Models\Permission::find($permissionId);
            if ($permission && in_array($permission->name, $adminOnlyPermissions) && !$user->hasPermissionTo('manage-permissions')) {
                return false;
            }
        }

        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Trim el nombre y email antes de validar
        $this->merge([
            'name' => trim($this->input('name', '')),
            'email' => trim($this->input('email', '')),
        ]);
    }
}

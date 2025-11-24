<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\Status;

class StatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $id = $this->segment(count($this->segments()));

        return [
            'nombre' => 'required|string|max:255|unique:statuses,nombre,' . $id . ',id',
            'descripcion' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'Status name is required',
            'nombre.string' => 'Status name must be a string',
            'nombre.max' => 'Status name cannot exceed 255 characters',
            'nombre.unique' => 'A status with this name already exists',
            'descripcion.string' => 'Description must be a string',
            'descripcion.max' => 'Description cannot exceed 1000 characters',
        ];
    }

    protected function failedValidation(Validator $validator): array
    {
        $nombre = $this->input('nombre');
        $descripcion = $this->input('descripcion');

        $response = [
            'severity' => 'error',
            'summary' => 'Validation Error',
            'detail' => 'Error in the validation of status data',
            'nombre' => $nombre,
            'descripcion' => $descripcion,
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
            $nombre = trim($this->input('nombre', ''));

            // Validar que el nombre no esté vacío después de trim
            if (empty($nombre)) {
                $validator->errors()->add('nombre', 'Status name cannot be empty or contain only whitespace');
                return;
            }

            // Verificar duplicados ignorando mayúsculas/minúsculas
            $id = $this->segment(count($this->segments()));
            $existingStatus = Status::whereRaw('LOWER(nombre) = ?', [strtolower($nombre)])
                ->when($id, function ($query) use ($id) {
                    return $query->where('id', '!=', $id);
                })
                ->first();

            // if ($existingStatus) {
            //     $validator->errors()->add('nombre', 'A status with this name already exists (case-insensitive match)');
            // }
        });
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Trim el nombre antes de validar
        $this->merge([
            'nombre' => trim($this->input('nombre', '')),
        ]);
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @mixin \Illuminate\Http\Request
 * @method \Illuminate\Routing\Route|null route()
 */
class AuthRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * NOTA: Clase de ejemplo para hacer un Request personalizado
     * Usar este request en AuthController trae falsos positivos, por lo que no se incluye en el controlador
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var \Illuminate\Routing\Route|null $route */
        $route = $this->route();
        $routeName = $route ? $route->getName() : null;

        $rules = [];

        if ($routeName === 'auth.login') {
            $rules = [
                'email' => 'required|string|email',
                'password' => 'required|string',
            ];
        } elseif ($routeName === 'auth.register') {
            $rules = [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
            ];
        } elseif ($routeName === 'auth.logout') {
            $rules = [
                'token' => 'required|string',
            ];
        }

        return $rules;
    }
}

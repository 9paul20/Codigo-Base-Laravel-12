<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UserController extends Controller
{
    use AuthorizesRequests;

    const VALIDATION_ERROR = "Validation Error";

    public function index(Request $request): JsonResponse
    {
        if ($request->expectsJson()) {
            try {
                $this->authorize('viewAny', User::class);

                $users = User::with('status')->paginate(100);

                return response()->json(new UserResource($users), 200);
            } catch (\Throwable $th) {
                return response()->json([
                    "severity" => "error",
                    "summary" => "Error",
                    "detail" => "Error retrieving users",
                    "errors" => $th->getMessage()
                ], 422);
            }
        }
        return response()->json(['message' => 'The access for users is just for JSON request'], 400);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        if ($request->expectsJson()) {
            try {
                $user = User::findOrFail($id);

                $this->authorize('view', $user);

                $user->load('status');

                return response()->json([
                    'user' => new UserResource($user),
                    'roles' => $user->getRoleNames(),
                    'permissions' => [
                        'direct' => $user->getDirectPermissions()->pluck('name'),
                        'via_roles' => $user->getPermissionsViaRoles()->pluck('name'),
                        'all' => $user->getAllPermissions()->pluck('name'),
                    ],
                ], 200);
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                return response()->json([
                    'severity' => 'error',
                    'summary' => 'Resource Not Found',
                    'detail' => 'The requested User was not found',
                    'errors' => 'No User found with the specified ID'
                ], 404);
            } catch (\Throwable $th) {
                return response()->json([
                    "severity" => "error",
                    "summary" => "Error",
                    "detail" => "Error retrieving user",
                    "errors" => $th->getMessage()
                ], 422);
            }
        }
        return response()->json(['message' => 'The access for user details is just for JSON request'], 400);
    }

    public function store(UserRequest $request): JsonResponse
    {
        try {
            $this->authorize('create', User::class);

            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => bcrypt($request->input('password')),
                'status_id' => $request->input('status_id', 1), // Activo por defecto
            ]);

            // Asignar roles si se proporcionan, de lo contrario asignar 'user' por defecto
            if ($request->has('roles') && is_array($request->roles)) {
                $user->syncRoles($request->roles);
            } else {
                $user->assignRole('user'); // Asignar rol user por defecto
            }

            // Asignar permisos directos si se proporcionan
            if ($request->has('permissions') && is_array($request->permissions)) {
                $user->syncPermissions($request->permissions);
            }

            $user->load('status');

            return response()->json([
                'user' => new UserResource($user),
                'roles' => $user->getRoleNames(),
                'permissions' => [
                    'direct' => $user->getDirectPermissions()->pluck('name'),
                    'via_roles' => $user->getPermissionsViaRoles()->pluck('name'),
                    'all' => $user->getAllPermissions()->pluck('name'),
                ],
                'message' => 'User created successfully',
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                "severity" => "error",
                "summary" => "Error",
                "detail" => "Error creating user",
                "errors" => $th->getMessage()
            ], 422);
        }
    }

    public function update(UserRequest $request, string $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            $this->authorize('update', $user);

            $user->update([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'status_id' => $request->input('status_id', $user->status_id),
            ]);

            // Actualizar roles si se proporcionan
            if ($request->has('roles') && is_array($request->roles)) {
                $user->syncRoles($request->roles);
            }

            // Actualizar permisos directos si se proporcionan
            if ($request->has('permissions') && is_array($request->permissions)) {
                $user->syncPermissions($request->permissions);
            }

            $user->load('status');

            return response()->json([
                'user' => new UserResource($user),
                'roles' => $user->getRoleNames(),
                'permissions' => [
                    'direct' => $user->getDirectPermissions()->pluck('name'),
                    'via_roles' => $user->getPermissionsViaRoles()->pluck('name'),
                    'all' => $user->getAllPermissions()->pluck('name'),
                ],
                'message' => 'User updated successfully',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'severity' => 'error',
                'summary' => 'Resource Not Found',
                'detail' => 'The requested User was not found',
                'errors' => 'No User found with the specified ID'
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                "severity" => "error",
                "summary" => "Error",
                "detail" => "Error updating user",
                "errors" => $th->getMessage()
            ], 422);
        }
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        if ($request->expectsJson()) {
            try {
                $user = User::findOrFail($id);

                $this->authorize('delete', $user);

                // No permitir que un usuario se elimine a sí mismo
                if ($user->id === auth()->id()) {
                    return response()->json([
                        "severity" => "error",
                        "summary" => "Deletion Not Allowed",
                        "detail" => "Cannot delete your own account",
                    ], 422);
                }

                $user->delete();

                return response()->json([
                    'message' => 'User deleted successfully',
                ], 200);
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                return response()->json([
                    'severity' => 'error',
                    'summary' => 'Resource Not Found',
                    'detail' => 'The requested User was not found',
                    'errors' => 'No User found with the specified ID'
                ], 404);
            } catch (\Throwable $th) {
                return response()->json([
                    "severity" => "error",
                    "summary" => "Error",
                    "detail" => "Error deleting user",
                    "errors" => $th->getMessage()
                ], 422);
            }
        }
        return response()->json(['message' => 'The access for deleting user is just for JSON request'], 400);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use App\Http\Resources\RoleResource;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            try {
                $roles = Role::with('permissions')->paginate(10);

                return new RoleResource($roles);
            } catch (\Throwable $th) {
                return response()->json([
                    "severity" => "error",
                    "summary" => "Error",
                    "detail" => "Error retrieving roles",
                    "errors" => $th->getMessage()
                ], 422);
            }
        }
        return "The access for roles is just for JSON request";
    }

    public function show(Request $request, int $id): JsonResponse
    {
        if (!$request->expectsJson()) {
            return response()->json(['message' => 'The access for role details is just for JSON request'], 400);
        }

        try {
            $role = Role::with('permissions')->findOrFail($id);

            return response()->json([
                'role' => new RoleResource($role)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                "severity" => "error",
                "summary" => "Not Found",
                "detail" => "Role not found",
                "errors" => "No role found with ID {$id}"
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                "severity" => "error",
                "summary" => "Error",
                "detail" => "Error retrieving role",
                "errors" => $th->getMessage()
            ], 422);
        }
    }

    public function store(RoleRequest $request)
    {
        if ($request->expectsJson()) {
            try {
                $role = Role::create($request->validated());

                if (isset($request->validated()['permissions']) && is_array($request->validated()['permissions'])) {
                    $role->permissions()->sync($request->validated()['permissions']);
                }

                $role->refresh();
                $role->load('permissions');

                return response()->json([
                    'role' => new RoleResource($role),
                    'message' => 'Role created successfully'
                ], 201);
            } catch (\Throwable $th) {
                return response()->json([
                    "severity" => "error",
                    "summary" => "Error",
                    "detail" => "Error creating role",
                    "errors" => $th->getMessage()
                ], 422);
            }
        }
        return "The access for creating role is just for JSON request";
    }

    public function update(RoleRequest $request, int $id)
    {
        if ($request->expectsJson()) {
            try {
                $role = Role::findOrFail($id);

                $role->update($request->validated());

                if (isset($request->validated()['permissions']) && is_array($request->validated()['permissions'])) {
                    $role->permissions()->sync($request->validated()['permissions']);
                }

                $role->refresh();
                $role->load('permissions');

                return response()->json([
                    'role' => new RoleResource($role),
                    'message' => 'Role updated successfully'
                ], 200);
            } catch (ModelNotFoundException $e) {
                return response()->json([
                    "severity" => "error",
                    "summary" => "Not Found",
                    "detail" => "Role not found",
                    "errors" => "No role found with ID {$id}"
                ], 404);
            } catch (\Throwable $th) {
                return response()->json([
                    "severity" => "error",
                    "summary" => "Error",
                    "detail" => "Error updating role",
                    "errors" => $th->getMessage()
                ], 422);
            }
        }
        return "The access for updating role is just for JSON request";
    }

    public function destroy(Request $request, int $id)
    {
        if ($request->expectsJson()) {
            try {
                $role = Role::findOrFail($id);

                // Verificar si es un rol del sistema (no permitir eliminar roles críticos)
                $systemRoles = ['super admin', 'admin', 'user'];
                if (in_array(strtolower($role->name), $systemRoles)) {
                    return response()->json([
                        "severity" => "error",
                        "summary" => "Deletion Not Allowed",
                        "detail" => "Cannot delete system role '{$role->name}'. This role is required for the application to function properly.",
                    ], 422);
                }

                // Verificar si el rol está asignado a usuarios
                if ($role->users()->exists()) {
                    return response()->json([
                        "severity" => "error",
                        "summary" => "Deletion Not Allowed",
                        "detail" => "Cannot delete role because it is currently assigned to one or more users. Please reassign users to a different role before deleting.",
                    ], 422);
                }

                $role->delete();

                return response()->json([
                    'message' => 'Role deleted successfully'
                ], 200);
            } catch (ModelNotFoundException $e) {
                return response()->json([
                    "severity" => "error",
                    "summary" => "Not Found",
                    "detail" => "Role not found",
                    "errors" => "No role found with ID {$id}"
                ], 404);
            } catch (\Throwable $th) {
                return response()->json([
                    "severity" => "error",
                    "summary" => "Error",
                    "detail" => "Error deleting role",
                    "errors" => $th->getMessage()
                ], 422);
            }
        }
        return "The access for deleting role is just for JSON request";
    }
}

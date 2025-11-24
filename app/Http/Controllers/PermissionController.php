<?php

namespace App\Http\Controllers;

use App\Http\Requests\PermissionRequest;
use App\Http\Resources\PermissionResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            try {
                $permissions = Permission::paginate(10);

                return new PermissionResource($permissions);
            } catch (\Throwable $th) {
                return response()->json([
                    "severity" => "error",
                    "summary" => "Error",
                    "detail" => "Error retrieving permissions",
                    "errors" => $th->getMessage()
                ], 422);
            }
        }
        return "The access for permissions is just for JSON request";
    }

    public function show(Request $request, int $id)
    {
        if ($request->expectsJson()) {
            try {
                $permission = Permission::findOrFail($id);

                return new PermissionResource($permission);
            } catch (ModelNotFoundException $e) {
                return response()->json([
                    "severity" => "error",
                    "summary" => "Permission Not Found",
                    "detail" => "The requested permission does not exist",
                    "errors" => "No permission found with ID {$id}"
                ], 404);
            } catch (\Throwable $th) {
                return response()->json([
                    "severity" => "error",
                    "summary" => "Error",
                    "detail" => "Error retrieving permission",
                    "errors" => $th->getMessage()
                ], 422);
            }
        }
        return "The access for permission details is just for JSON request";
    }

    public function store(PermissionRequest $request)
    {
        if ($request->expectsJson()) {
            try {
                $permission = Permission::create($request->validated());

                return response()->json([
                    'permission' => new PermissionResource($permission),
                    'message' => 'Permission created successfully'
                ], 201);
            } catch (\Throwable $th) {
                return response()->json([
                    "severity" => "error",
                    "summary" => "Error",
                    "detail" => "Error creating permission",
                    "errors" => $th->getMessage()
                ], 422);
            }
        }
        return "The access for creating permission is just for JSON request";
    }

    public function update(PermissionRequest $request, int $id)
    {
        if ($request->expectsJson()) {
            try {
                $permission = Permission::findOrFail($id);

                $permission->update($request->validated());

                return response()->json([
                    'permission' => new PermissionResource($permission),
                    'message' => 'Permission updated successfully'
                ], 200);
            } catch (ModelNotFoundException $e) {
                return response()->json([
                    "severity" => "error",
                    "summary" => "Permission Not Found",
                    "detail" => "The requested permission does not exist",
                    "errors" => "No permission found with ID {$id}"
                ], 404);
            } catch (\Throwable $th) {
                return response()->json([
                    "severity" => "error",
                    "summary" => "Error",
                    "detail" => "Error updating permission",
                    "errors" => $th->getMessage()
                ], 422);
            }
        }
        return "The access for updating permission is just for JSON request";
    }

    public function destroy(Request $request, int $id)
    {
        if ($request->expectsJson()) {
            try {
                $permission = Permission::findOrFail($id);

                // Verificar si el permiso está asignado directamente a usuarios
                if ($permission->users()->exists()) {
                    return response()->json([
                        "severity" => "error",
                        "summary" => "Deletion Not Allowed",
                        "detail" => "Cannot delete permission because it is directly assigned to one or more users. Please remove the permission from users before deleting.",
                    ], 422);
                }

                // Verificar si el permiso está asignado a roles
                if ($permission->roles()->exists()) {
                    return response()->json([
                        "severity" => "error",
                        "summary" => "Deletion Not Allowed",
                        "detail" => "Cannot delete permission because it is assigned to one or more roles. Please remove the permission from roles before deleting.",
                    ], 422);
                }

                $permission->delete();

                return response()->json([
                    'message' => 'Permission deleted successfully',
                ], 200);
            } catch (ModelNotFoundException $e) {
                return response()->json([
                    "severity" => "error",
                    "summary" => "Permission Not Found",
                    "detail" => "The requested permission does not exist",
                    "errors" => "No permission found with ID {$id}"
                ], 404);
            } catch (\Throwable $th) {
                return response()->json([
                    "severity" => "error",
                    "summary" => "Error",
                    "detail" => "Error deleting permission",
                    "errors" => $th->getMessage()
                ], 422);
            }
        }
        return "The access for deleting permission is just for JSON request";
    }
}

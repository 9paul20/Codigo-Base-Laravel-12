<?php

namespace App\Http\Controllers;

use App\Http\Requests\StatusRequest;
use App\Http\Resources\StatusResource;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class StatusController extends Controller
{
    use AuthorizesRequests;

    const VALIDATION_ERROR = "Validation Error";

    public function index(Request $request): JsonResponse
    {
        if ($request->expectsJson()) {
            try {
                $statuses = Status::paginate(10);

                return response()->json(new StatusResource($statuses), 200);
            } catch (\Throwable $th) {
                return response()->json([
                    "severity" => "error",
                    "summary" => "Error",
                    "detail" => "Error retrieving statuses",
                    "errors" => $th->getMessage()
                ], 422);
            }
        }
        return response()->json(['message' => 'The access for statuses is just for JSON request'], 400);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        if ($request->expectsJson()) {
            try {
                $status = Status::findOrFail($id);

                $this->authorize('view', $status);

                return response()->json([
                    'status' => new StatusResource($status),
                ], 200);
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                return response()->json([
                    'severity' => 'error',
                    'summary' => 'Resource Not Found',
                    'detail' => 'The requested Status was not found',
                    'errors' => 'No Status found with the specified ID'
                ], 404);
            } catch (\Throwable $th) {
                return response()->json([
                    "severity" => "error",
                    "summary" => "Error",
                    "detail" => "Error retrieving status",
                    "errors" => $th->getMessage()
                ], 422);
            }
        }
        return response()->json(['message' => 'The access for status details is just for JSON request'], 400);
    }

    public function store(StatusRequest $request): JsonResponse
    {
        try {
            $this->authorize('create', Status::class);

            $status = Status::create([
                'nombre' => $request->input('nombre'),
                'descripcion' => $request->input('descripcion'),
            ]);

            return response()->json([
                'status' => new StatusResource($status),
                'message' => 'Status created successfully',
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                "severity" => "error",
                "summary" => "Error",
                "detail" => "Error creating status",
                "errors" => $th->getMessage()
            ], 422);
        }
    }

    public function update(StatusRequest $request, int $id): JsonResponse
    {
        try {
            $status = Status::findOrFail($id);

            $this->authorize('update', $status);

            $status->update([
                'nombre' => $request->input('nombre'),
                'descripcion' => $request->input('descripcion'),
            ]);

            return response()->json([
                'status' => new StatusResource($status->fresh()),
                'message' => 'Status updated successfully',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'severity' => 'error',
                'summary' => 'Resource Not Found',
                'detail' => 'The requested Status was not found',
                'errors' => 'No Status found with the specified ID'
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                "severity" => "error",
                "summary" => "Error",
                "detail" => "Error updating status",
                "errors" => $th->getMessage()
            ], 422);
        }
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        if ($request->expectsJson()) {
            try {
                $status = Status::findOrFail($id);

                $this->authorize('delete', $status);

                // Verificar si hay usuarios usando este status
                if ($status->user()->exists()) {
                    return response()->json([
                        "severity" => "error",
                        "summary" => "Deletion Not Allowed",
                        "detail" => "Cannot delete status because it is currently assigned to one or more users. Please reassign users to a different status before deleting.",
                    ], 422);
                }

                $status->delete();

                return response()->json([
                    'message' => 'Status deleted successfully',
                ], 200);
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                return response()->json([
                    'severity' => 'error',
                    'summary' => 'Resource Not Found',
                    'detail' => 'The requested Status was not found',
                    'errors' => 'No Status found with the specified ID'
                ], 404);
            } catch (\Throwable $th) {
                return response()->json([
                    "severity" => "error",
                    "summary" => "Error",
                    "detail" => "Error deleting status",
                    "errors" => $th->getMessage()
                ], 422);
            }
        }
        return response()->json(['message' => 'The access for deleting status is just for JSON request'], 400);
    }
}

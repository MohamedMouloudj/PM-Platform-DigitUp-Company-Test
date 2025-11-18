<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\DTOs\Permission\GrantPermissionDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Permission\GrantPermissionRequest;
use App\Services\Permission\PermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProjectPermissionController extends Controller
{
    public function __construct(
        private readonly PermissionService $permissionService,
    ) {}

    /**
     * Get all permissions for a project
     */
    public function index(Request $request, string $projectId): JsonResponse
    {
        try {
            $permissions = $this->permissionService->getProjectPermissions($projectId, $request->user());

            return response()->json([
                'success' => true,
                'data' => $permissions,
            ]);
        } catch (NotFoundHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        } catch (AccessDeniedHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        }
    }

    /**
     * Grant permission to a user on a project
     */
    public function store(GrantPermissionRequest $request, string $projectId): JsonResponse
    {
        try {
            $dto = GrantPermissionDTO::fromRequest($request->validated(), $request->user()->id);

            $permission = $this->permissionService->grantPermission($projectId, $dto, $request->user());

            return response()->json([
                'success' => true,
                'data' => $permission,
                'message' => 'Permission granted successfully',
            ], 201);
        } catch (NotFoundHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        } catch (AccessDeniedHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        }
    }

    /**
     * Revoke permission from a user on a project
     */
    public function destroy(Request $request, string $projectId, string $userId): JsonResponse
    {
        try {
            $this->permissionService->revokePermission($projectId, $userId, $request->user());

            return response()->json([
                'success' => true,
                'message' => 'Permission revoked successfully',
            ], 204);
        } catch (NotFoundHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        } catch (AccessDeniedHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\DTOs\Project\CreateProjectDTO;
use App\DTOs\Project\UpdateProjectDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Project\CreateProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Services\Project\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProjectController extends Controller
{
    public function __construct(
        private readonly ProjectService $projectService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $projects = $this->projectService->getUserProjects($user);

            return response()->json([
                'success' => true,
                'data' => ['projects' => $projects],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve projects',
                'errors' => ['error' => [$e->getMessage()]],
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateProjectRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $dto = CreateProjectDTO::fromRequest($request->validated(), $user->id);
            $project = $this->projectService->create($dto, $user);

            return response()->json([
                'success' => true,
                'message' => 'Project created successfully',
                'data' => ['project' => $project],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create project',
                'errors' => ['error' => [$e->getMessage()]],
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $user = $request->user();
            $project = $this->projectService->getById($id, $user);

            return response()->json([
                'success' => true,
                'data' => ['project' => $project],
            ]);
        } catch (NotFoundHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found',
            ], 404);
        } catch (AccessDeniedHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied',
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred',
                'errors' => ['error' => [$e->getMessage()]],
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectRequest $request, string $id): JsonResponse
    {
        try {
            $user = $request->user();
            $dto = UpdateProjectDTO::fromRequest($request->validated());
            $project = $this->projectService->update($id, $dto, $user);

            return response()->json([
                'success' => true,
                'message' => 'Project updated successfully',
                'data' => ['project' => $project],
            ]);
        } catch (NotFoundHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found',
            ], 404);
        } catch (AccessDeniedHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied',
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update project',
                'errors' => ['error' => [$e->getMessage()]],
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $user = $request->user();
            $this->projectService->delete($id, $user);

            return response()->json([
                'success' => true,
                'message' => 'Project deleted successfully',
            ]);
        } catch (NotFoundHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found',
            ], 404);
        } catch (AccessDeniedHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied',
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete project',
                'errors' => ['error' => [$e->getMessage()]],
            ], 500);
        }
    }

    /**
     * Archive a project
     */
    public function archive(Request $request, string $id): JsonResponse
    {
        try {
            $user = $request->user();
            $project = $this->projectService->archive($id, $user);

            return response()->json([
                'success' => true,
                'message' => 'Project archived successfully',
                'data' => ['project' => $project],
            ]);
        } catch (NotFoundHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found',
            ], 404);
        } catch (AccessDeniedHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied',
            ], 403);
        }
    }

    /**
     * Restore an archived project
     */
    public function restore(Request $request, string $id): JsonResponse
    {
        try {
            $user = $request->user();
            $project = $this->projectService->restore($id, $user);

            return response()->json([
                'success' => true,
                'message' => 'Project restored successfully',
                'data' => ['project' => $project],
            ]);
        } catch (NotFoundHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found',
            ], 404);
        } catch (AccessDeniedHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied',
            ], 403);
        }
    }
}

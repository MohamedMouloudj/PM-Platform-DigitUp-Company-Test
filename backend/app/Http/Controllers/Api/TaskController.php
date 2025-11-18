<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\DTOs\Task\AssignTaskDTO;
use App\DTOs\Task\CreateTaskDTO;
use App\DTOs\Task\UpdateTaskDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Task\AssignTaskRequest;
use App\Http\Requests\Task\CreateTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Services\Task\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskService $taskService,
    ) {}

    /**
     * Get all tasks accessible by the user (across all projects)
     */
    public function allTasks(Request $request): JsonResponse
    {
        try {
            $tasks = $this->taskService->getAllUserTasks($request->user());

            return response()->json([
                'success' => true,
                'data' => $tasks,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve tasks',
            ], 500);
        }
    }

    /**
     * Get all tasks for a specific project
     */
    public function index(Request $request, string $projectId): JsonResponse
    {
        try {
            $tasks = $this->taskService->getProjectTasks($projectId, $request->user());

            return response()->json([
                'success' => true,
                'data' => $tasks,
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
     * Create a new task
     */
    public function store(CreateTaskRequest $request, string $projectId): JsonResponse
    {
        try {
            $dto = CreateTaskDTO::fromRequest(
                $request->validated(),
                $projectId,
                $request->user()->id
            );

            $task = $this->taskService->create($dto, $request->user());

            return response()->json([
                'success' => true,
                'data' => $task,
                'message' => 'Task created successfully',
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
     * Get a specific task
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $task = $this->taskService->getById($id, $request->user());

            return response()->json([
                'success' => true,
                'data' => $task,
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
     * Update a task
     */
    public function update(UpdateTaskRequest $request, string $id): JsonResponse
    {
        try {
            $dto = UpdateTaskDTO::fromRequest($request->validated());

            $task = $this->taskService->update($id, $dto, $request->user());

            return response()->json([
                'success' => true,
                'data' => $task,
                'message' => 'Task updated successfully',
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
     * Delete a task
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $this->taskService->delete($id, $request->user());

            return response()->json([
                'success' => true,
                'message' => 'Task deleted successfully',
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

    /**
     * Assign a task to a user
     */
    public function assign(AssignTaskRequest $request, string $id): JsonResponse
    {
        try {
            $dto = AssignTaskDTO::fromRequest($request->validated());

            $task = $this->taskService->assign($id, $dto, $request->user());

            return response()->json([
                'success' => true,
                'data' => $task,
                'message' => 'Task assigned successfully',
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
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\DTOs\Comment\CreateCommentDTO;
use App\DTOs\Comment\UpdateCommentDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Comment\CreateCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use App\Services\Comment\CommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CommentController extends Controller
{
    public function __construct(
        private readonly CommentService $commentService,
    ) {}

    /**
     * Get all comments for a task
     */
    public function index(Request $request, string $taskId): JsonResponse
    {
        try {
            $comments = $this->commentService->getTaskComments($taskId, $request->user());

            return response()->json([
                'success' => true,
                'data' => $comments,
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
     * Create a new comment
     */
    public function store(CreateCommentRequest $request, string $taskId): JsonResponse
    {
        try {
            $dto = CreateCommentDTO::fromRequest(
                $request->validated(),
                $taskId,
                $request->user()->id
            );

            $comment = $this->commentService->create($dto, $request->user());

            return response()->json([
                'success' => true,
                'data' => $comment,
                'message' => 'Comment created successfully',
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
     * Get a specific comment
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $comment = $this->commentService->getById($id, $request->user());

            return response()->json([
                'success' => true,
                'data' => $comment,
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
     * Update a comment
     */
    public function update(UpdateCommentRequest $request, string $id): JsonResponse
    {
        try {
            $dto = UpdateCommentDTO::fromRequest($request->validated());

            $comment = $this->commentService->update($id, $dto, $request->user());

            return response()->json([
                'success' => true,
                'data' => $comment,
                'message' => 'Comment updated successfully',
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
     * Delete a comment
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $this->commentService->delete($id, $request->user());

            return response()->json([
                'success' => true,
                'message' => 'Comment deleted successfully',
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

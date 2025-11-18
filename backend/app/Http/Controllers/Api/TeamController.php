<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\DTOs\Team\AddMemberDTO;
use App\DTOs\Team\CreateTeamDTO;
use App\DTOs\Team\UpdateTeamDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Team\AddMemberRequest;
use App\Http\Requests\Team\CreateTeamRequest;
use App\Http\Requests\Team\UpdateTeamRequest;
use App\Services\Team\TeamService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TeamController extends Controller
{
    public function __construct(
        private readonly TeamService $teamService,
    ) {}

    /**
     * Get all teams for the authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $teams = $this->teamService->getUserTeams($request->user());

        return response()->json([
            'success' => true,
            'data' => $teams,
        ]);
    }

    /**
     * Create a new team
     */
    public function store(CreateTeamRequest $request): JsonResponse
    {
        $dto = CreateTeamDTO::fromRequest($request->validated(), $request->user()->id);

        $team = $this->teamService->create($dto, $request->user());

        return response()->json([
            'success' => true,
            'data' => $team,
            'message' => 'Team created successfully',
        ], 201);
    }

    /**
     * Get a specific team
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $team = $this->teamService->getById($id, $request->user());

            return response()->json([
                'success' => true,
                'data' => $team,
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
     * Update a team
     */
    public function update(UpdateTeamRequest $request, string $id): JsonResponse
    {
        try {
            $dto = UpdateTeamDTO::fromRequest($request->validated());

            $team = $this->teamService->update($id, $dto, $request->user());

            return response()->json([
                'success' => true,
                'data' => $team,
                'message' => 'Team updated successfully',
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
     * Delete a team
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $this->teamService->delete($id, $request->user());

            return response()->json([
                'success' => true,
                'message' => 'Team deleted successfully',
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
     * Add a member to a team
     */
    public function addMember(AddMemberRequest $request, string $id): JsonResponse
    {
        try {
            $dto = AddMemberDTO::fromRequest($request->validated());

            $team = $this->teamService->addMember($id, $dto, $request->user());

            return response()->json([
                'success' => true,
                'data' => $team,
                'message' => 'Member added successfully',
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
     * Remove a member from a team
     */
    public function removeMember(Request $request, string $teamId, string $userId): JsonResponse
    {
        try {
            $team = $this->teamService->removeMember($teamId, $userId, $request->user());

            return response()->json([
                'success' => true,
                'data' => $team,
                'message' => 'Member removed successfully',
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

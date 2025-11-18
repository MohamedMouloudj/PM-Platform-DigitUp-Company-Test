<?php

declare(strict_types=1);

namespace App\Services\Team;

use App\DTOs\Team\AddMemberDTO;
use App\DTOs\Team\CreateTeamDTO;
use App\DTOs\Team\UpdateTeamDTO;
use App\Enums\UserRole;
use App\Models\Team;
use App\Models\User;
use App\Repositories\Contracts\TeamRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TeamService
{
    public function __construct(
        private readonly TeamRepositoryInterface $teamRepository,
    ) {}

    public function create(CreateTeamDTO $dto, User $user): Team
    {
        return DB::transaction(function () use ($dto, $user) {
            $team = $this->teamRepository->create([
                'name' => $dto->name,
                'description' => $dto->description,
                'created_by' => $dto->created_by,
            ]);

            activity()
                ->causedBy($user)
                ->performedOn($team)
                ->log('Team created');

            return $team;
        });
    }

    public function update(string $teamId, UpdateTeamDTO $dto, User $user): Team
    {
        $team = $this->getTeamWithAccessCheck($teamId, $user, requireCreatorOrAdmin: true);

        return DB::transaction(function () use ($team, $dto, $user) {
            $team = $this->teamRepository->update($team, $dto->toArray());

            activity()
                ->causedBy($user)
                ->performedOn($team)
                ->log('Team updated');

            return $team;
        });
    }

    public function delete(string $teamId, User $user): void
    {
        $team = $this->getTeamWithAccessCheck($teamId, $user, requireCreatorOrAdmin: true);

        DB::transaction(function () use ($team, $user) {
            activity()
                ->causedBy($user)
                ->performedOn($team)
                ->withProperties(['team_name' => $team->name])
                ->log('Team deleted');

            $this->teamRepository->delete($team);
        });
    }

    public function getById(string $teamId, User $user): Team
    {
        return $this->getTeamWithAccessCheck($teamId, $user, requireCreatorOrAdmin: false);
    }

    public function getUserTeams(User $user): Collection
    {
        return $this->teamRepository->getUserTeams($user);
    }

    public function addMember(string $teamId, AddMemberDTO $dto, User $user): Team
    {
        $team = $this->getTeamWithAccessCheck($teamId, $user, requireCreatorOrAdmin: true);

        DB::transaction(function () use ($team, $dto, $user) {
            $this->teamRepository->addMember($team, $dto->user_id);

            activity()
                ->causedBy($user)
                ->performedOn($team)
                ->withProperties(['added_user_id' => $dto->user_id])
                ->log('Member added to team');
        });

        return $this->teamRepository->findById($teamId);
    }

    public function removeMember(string $teamId, string $userId, User $user): Team
    {
        $team = $this->getTeamWithAccessCheck($teamId, $user, requireCreatorOrAdmin: true);

        // Prevent removing the creator
        if ($team->created_by === $userId) {
            throw new AccessDeniedHttpException('Cannot remove team creator from team');
        }

        DB::transaction(function () use ($team, $userId, $user) {
            $this->teamRepository->removeMember($team, $userId);

            activity()
                ->causedBy($user)
                ->performedOn($team)
                ->withProperties(['removed_user_id' => $userId])
                ->log('Member removed from team');
        });

        return $this->teamRepository->findById($teamId);
    }

    private function getTeamWithAccessCheck(string $teamId, User $user, bool $requireCreatorOrAdmin): Team
    {
        $team = $this->teamRepository->findById($teamId);

        if (!$team) {
            throw new NotFoundHttpException('Team not found');
        }

        if ($requireCreatorOrAdmin) {
            // Only creator or admin can modify
            if ($user->role !== UserRole::ADMIN && $team->created_by !== $user->id) {
                throw new AccessDeniedHttpException('Only team creator or admin can perform this action');
            }
        } else {
            // For read access, check if user is creator, member, or admin
            $isMember = $this->teamRepository->isMember($team, $user->id);
            if ($user->role !== UserRole::ADMIN && $team->created_by !== $user->id && !$isMember) {
                throw new AccessDeniedHttpException('You do not have access to this team');
            }
        }

        return $team;
    }
}

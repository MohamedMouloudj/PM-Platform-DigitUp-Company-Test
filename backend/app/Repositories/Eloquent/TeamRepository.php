<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Enums\UserRole;
use App\Models\Team;
use App\Models\User;
use App\Repositories\Contracts\TeamRepositoryInterface;
use Illuminate\Support\Collection;

class TeamRepository implements TeamRepositoryInterface
{
    public function create(array $data): Team
    {
        return Team::create($data);
    }

    public function update(Team $team, array $data): Team
    {
        $team->update($data);
        return $team->fresh();
    }

    public function delete(Team $team): bool
    {
        return $team->delete();
    }

    public function findById(string $id): ?Team
    {
        return Team::with(['creator', 'members', 'projects'])
            ->find($id);
    }

    public function getUserTeams(User $user): Collection
    {
        if ($user->role === UserRole::ADMIN) {
            return Team::with(['creator', 'members'])->get();
        }

        // Get teams where user is creator OR user is a member
        return Team::with(['creator', 'members'])
            ->where(function ($query) use ($user) {
                $query->where('created_by', $user->id)
                    ->orWhereHas('members', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function addMember(Team $team, string $userId): void
    {
        if (!$this->isMember($team, $userId)) {
            $team->members()->attach($userId, [
                'joined_at' => now(),
            ]);
        }
    }

    public function removeMember(Team $team, string $userId): void
    {
        $team->members()->detach($userId);
    }

    public function isMember(Team $team, string $userId): bool
    {
        return $team->members()->where('user_id', $userId)->exists();
    }
}

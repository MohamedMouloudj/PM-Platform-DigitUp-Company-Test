<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Collection;

interface TeamRepositoryInterface
{
    public function create(array $data): Team;

    public function update(Team $team, array $data): Team;

    public function delete(Team $team): bool;

    public function findById(string $id): ?Team;

    public function getUserTeams(User $user): Collection;

    public function addMember(Team $team, string $userId): void;

    public function removeMember(Team $team, string $userId): void;

    public function isMember(Team $team, string $userId): bool;
}

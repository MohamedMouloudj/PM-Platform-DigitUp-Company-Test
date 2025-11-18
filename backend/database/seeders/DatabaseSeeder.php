<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\FileValidation;
use App\Models\Project;
use App\Models\ProjectPermission;
use App\Models\ProjectTeam;
use App\Models\SecurityAlert;
use App\Models\Task;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        // Create manager user
        $manager = User::factory()->manager()->create([
            'name' => 'Manager User',
            'email' => 'manager@example.com',
        ]);

        // Create regular users
        $users = User::factory(10)->create();

        // Create teams
        $teams = Team::factory(3)->create([
            'created_by' => $admin->id,
        ]);

        // Add team members (ensure unique user per team)
        foreach ($teams as $team) {
            $selectedUsers = $users->random(5);
            foreach ($selectedUsers as $user) {
                TeamMember::factory()->create([
                    'team_id' => $team->id,
                    'user_id' => $user->id,
                ]);
            }
        }

        // Create projects
        $projects = Project::factory(5)->create([
            'created_by' => $admin->id,
        ]);

        // Add confidential and top secret projects
        Project::factory()->confidential()->create([
            'created_by' => $manager->id,
            'name' => 'Confidential Project',
        ]);

        Project::factory()->topSecret()->create([
            'created_by' => $admin->id,
            'name' => 'Top Secret Project',
        ]);

        // Assign teams to projects
        foreach ($projects->take(3) as $project) {
            ProjectTeam::factory()->create([
                'project_id' => $project->id,
                'team_id' => $teams->random()->id,
            ]);
        }

        // Create project permissions
        foreach ($projects as $project) {
            $selectedUsers = $users->random(3);
            foreach ($selectedUsers as $user) {
                ProjectPermission::factory()->create([
                    'project_id' => $project->id,
                    'user_id' => $user->id,
                    'granted_by' => $admin->id,
                ]);
            }
        }

        // Create tasks
        foreach ($projects as $project) {
            Task::factory(8)->create([
                'project_id' => $project->id,
                'created_by' => $admin->id,
                'assigned_to' => $users->random()->id,
            ]);

            // Create some unassigned tasks
            Task::factory(2)->unassigned()->create([
                'project_id' => $project->id,
                'created_by' => $manager->id,
            ]);
        }

        // Create comments on tasks
        $tasks = Task::all();
        foreach ($tasks->take(20) as $task) {
            Comment::factory(rand(1, 5))->create([
                'task_id' => $task->id,
                'user_id' => $users->random()->id,
            ]);

            // Some comments with file attachments
            if (rand(0, 1)) {
                Comment::factory()->withFile()->create([
                    'task_id' => $task->id,
                    'user_id' => $users->random()->id,
                ]);
            }
        }

        // Create security alerts
        SecurityAlert::factory(5)->suspiciousLogin()->create([
            'user_id' => $users->random()->id,
        ]);

        SecurityAlert::factory(3)->newLocation()->create([
            'user_id' => $users->random()->id,
        ]);

        // Create file validations
        FileValidation::factory(10)->clean()->create([
            'uploaded_by' => $users->random()->id,
        ]);

        FileValidation::factory(2)->pending()->create([
            'uploaded_by' => $users->random()->id,
        ]);

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin: admin@example.com');
        $this->command->info('Manager: manager@example.com');
        $this->command->info('Password for all users: password');
    }
}

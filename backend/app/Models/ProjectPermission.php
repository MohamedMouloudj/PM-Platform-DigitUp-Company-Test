<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PermissionType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProjectPermission extends Model
{
    use HasFactory, HasUuids, LogsActivity;

    protected $fillable = [
        'project_id',
        'user_id',
        'permission',
        'granted_by',
        'granted_at',
    ];

    protected function casts(): array
    {
        return [
            'permission' => PermissionType::class,
            'granted_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['permission', 'user_id', 'granted_by'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }
}

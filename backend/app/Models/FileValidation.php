<?php

namespace App\Models;

use App\Enums\FileScanStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileValidation extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'file_path',
        'original_name',
        'mime_type',
        'size',
        'hash',
        'scan_status',
        'scan_result',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'scan_status' => FileScanStatus::class,
            'size' => 'integer',
        ];
    }

    // Relationships
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}

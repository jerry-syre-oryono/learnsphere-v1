<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_id',
        'title',
        'description',
        'due_date',
        'max_score',
        'weight',
        'attachment_path',
        'attachment_name',
    ];

    protected $casts = [
        'due_date' => 'datetime',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class OnboardingProject extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_code',
        'customer_name',
        'site_name',
        'status',
        'progress_percent',
        'questionnaire_answers',
        'pics_data',
        'uploaded_files',
        'internal_pic_id',
        'internal_notes',
        'last_interaction_at'
    ];

    protected $casts = [
        'questionnaire_answers' => 'array',
        'pics_data' => 'array',
        'uploaded_files' => 'array',
        'last_interaction_at' => 'datetime',
    ];

    public function internalPic()
    {
        return $this->belongsTo(User::class, 'internal_pic_id');
    }
}

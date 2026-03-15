<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TyreMonitoringImage extends Model
{
    use HasFactory;

    protected $table = 'tyre_monitoring_images';
    protected $primaryKey = 'image_id';

    protected $fillable = [
        'session_id',
        'check_id',
        'serial_number',
        'image_type',
        'image_path',
        'original_name',
        'notes',
        'uploaded_by',
    ];

    public function session()
    {
        return $this->belongsTo(TyreMonitoringSession::class, 'session_id', 'session_id');
    }

    public function check()
    {
        return $this->belongsTo(TyreMonitoringCheck::class, 'check_id', 'check_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}

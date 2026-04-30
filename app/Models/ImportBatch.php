<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'tyre_company_id', 'module', 'filename', 'status', 
        'approved_by', 'approved_at', 'total_rows', 
        'processed_rows', 'notes'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(ImportItem::class, 'batch_id');
    }
}

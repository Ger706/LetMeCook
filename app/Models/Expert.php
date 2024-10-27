<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expert extends Model
{
    use HasFactory;

    protected $table = 'experts';
    protected $primaryKey = 'expert_id';
    protected $fillable = [
        'expert_name',
        'expert_address',
        'expert_profile',
        'expert_job_desc',
        'expert_major',
        'specialization',
    ];
}

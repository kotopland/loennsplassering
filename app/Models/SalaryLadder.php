<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryLadder extends Model
{
    use HasFactory;

    protected $fillable = [
        'ladder',
        'group',
        'salaries',
    ];

    protected $casts = [
        'salaries' => 'array',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'logo',
        'name',
        'slogan',
        'address',
        'email',
        'phone_one',
        'phone_two'
    ];

    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at'
    ];
}

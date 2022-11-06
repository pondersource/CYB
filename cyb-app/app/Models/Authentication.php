<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Authentication extends Model
{
    protected $table = 'authentications';

    protected $fillable = ['app_code_name', 'display_name', 'app_user_id', 'user_id', 'metadata'];

    use HasFactory;
}

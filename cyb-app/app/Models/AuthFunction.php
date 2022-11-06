<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthFunction extends Model
{
    protected $table = 'authfunctions';

    protected $fillable = ['auth_id', 'data_type', 'read', 'write'];

    use HasFactory;
}

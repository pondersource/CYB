<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecurringTask extends Model
{
    protected $table = 'recurring_tasks';

    protected $fillable = ['interval', 'function', 'parameters'];

    use HasFactory;
}

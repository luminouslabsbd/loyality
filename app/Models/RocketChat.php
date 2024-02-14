<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RocketChat extends Model
{
    use HasFactory;
    
    protected $table = 'rocket_chat';

    protected $fillable=['api_url','api_title','api_token','x_user_id'];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Officer extends Model
{
    use HasFactory;
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'title',
        'address1',
        'address2',
        'city',
        'state',
        'zip',
        'country',
        'entity_id',
    ];
}

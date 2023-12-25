<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegisteredAgent extends Model
{
    use HasFactory;

    protected $fillable = [
        'entity_name',
        'company_name',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address1',
        'address2',
        'city',
        'state',
        'zip',
        'country',
        'entity_id',
    ];
}

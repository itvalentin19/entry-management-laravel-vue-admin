<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entity extends Model
{
    use HasFactory;

    protected $fillable = [
        'firm_name',
        'doing_business_as',
        'entity_name',
        'address_1',
        'address_2',
        'city',
        'state',
        'zip',
        'country',
        'type',
        'services',
        'annual_fees',
        'first_tax_year',
        'directors',
        'ein_number',
        'form_id',
        'date_created',
        'date_signed',
        'person',
        'jurisdiction',
        'notes',
        'ref_by',
        'user_id',
        'active',
    ];
    protected $casts = [
        'directors' => 'boolean',
        'form_id' => 'boolean',
        'owner_ids' => 'array',
        'document_ids' => 'array',
        'ref_by' => 'array',
        'services' => 'array',
        'annual_fees' => 'array',
        'active' => 'boolean',
    ];

    // Relationship to Owner
    // public function ownerList()
    // {
    //     return $this->hasMany(Owner::class, 'id', 'owners');
    // }
    public function get_owners($ids)
    {
        return Owner::whereIn('id', $ids)->get();
    }

    // Relationship to Document
    public function documents()
    {
        return $this->hasMany(Document::class, 'entity_id', 'id');
    }
}

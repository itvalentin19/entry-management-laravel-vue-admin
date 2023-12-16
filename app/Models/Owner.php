<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Owner extends Model
{
    use HasFactory;
    protected $fillable = [
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
        'ownership_stake',
        'document_type',
        'document_expiration',
        'user_id'
    ];

    // Relationship to Entity
    public function entities()
    {
        return $this->belongsTo(Entity::class);
    }
    // Relationship to Document
    public function documents()
    {
        return $this->belongsToMany(Document::class, 'documents', 'owners.kyc_document', 'id');
    }

    // If it's a one-to-one relationship
    public function kycDocument()
    {
        return $this->hasOne(Document::class, 'id', 'kyc_document');
    }
}

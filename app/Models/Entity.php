<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entity extends Model
{
    use HasFactory;
    protected $casts = [
        'services' => 'array',
        'directors' => 'boolean',
        'notes' => 'array',
    ];

    // Relationship to Owner
    public function owners()
    {
        return $this->belongsToMany(Owner::class, 'owners', 'id', 'id');
    }

    // Relationship to Document
    public function documents()
    {
        return $this->belongsToMany(Document::class, 'documents', 'entity_id', 'id');
    }
}

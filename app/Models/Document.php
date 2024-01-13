<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;
// public function entities()
// {
//     return $this->belongsToMany(Entity::class, 'documents', 'id', 'entity_id');
// }
// public function owners()
// {
//     return $this->belongsToMany(Owner::class, 'documents', 'id', 'owner_id');
// }

// // If it's a one-to-one relationship
// public function owner()
// {
//     return $this->belongsTo(Owner::class, 'kyc_document');
// }

}

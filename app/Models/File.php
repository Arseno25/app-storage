<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class File extends Model
{
   protected $fillable = ['title', 'document_word','document_pdf', 'user_id', 'description', 'status'];

   public function user():BelongsTo
   {
    return $this->belongsTo(User::class);
   }
}

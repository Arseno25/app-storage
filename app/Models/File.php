<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class File extends Model implements HasMedia
{

    use HasFactory, InteractsWithMedia;
   protected $fillable = ['title', 'document_word','document_pdf', 'user_id', 'description', 'status', 'completed_at', 'admin_id'];

   protected $casts = [
       'completed_at' => 'datetime',
   ];
   public function user():BelongsTo
   {
    return $this->belongsTo(User::class, 'user_id');
   }

    public function userAdmin():BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }



    public function deletePhysicalFiles(): void
    {
        $folderPath = 'documents/' . \Str::slug($this->title);

        if (Storage::exists($folderPath)) {
            Storage::deleteDirectory($folderPath);
        }
    }
}

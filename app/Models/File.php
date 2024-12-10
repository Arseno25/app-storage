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

    public function getDocumentPdfNameAttribute(): string
    {
        return basename($this->document_pdf);
    }

    public function getDocumentWordNameAttribute(): string
    {
        return basename($this->document_word);
    }

    public function deletePhysicalFiles(): void
    {
        $folderPath = 'documents/' . \Str::slug($this->title);

        try {
            if (Storage::disk('public')->exists($folderPath)) {
                $files = Storage::disk('public')->files($folderPath);
                if (!empty($files)) {
                    Storage::disk('public')->delete($files);
                }
                Storage::disk('public')->deleteDirectory($folderPath);
            } else {
                throw new \Exception("Folder tidak ditemukan: " . $folderPath);
            }
        } catch (\Exception $e) {
            \Log::error("Error deleting folder: " . $e->getMessage());
        }
    }
}

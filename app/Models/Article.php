<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 0;

    public const STATUS_PUBLISHED = 1;

    protected $fillable = ['user_id', 'title', 'slug', 'content', 'image', 'status'];

    protected $casts = [
        'status' => 'integer',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    // slug cuma dibuat sekali saat create, biar URL lama tidak rusak kalau judul diedit
    protected static function booted(): void
    {
        static::creating(function (Article $article) {
            if (blank($article->slug)) {
                $article->slug = static::generateUniqueSlug($article->title);
            }
        });
    }

    // judul kembar jadi judul-2, judul-3, dst.
    public static function generateUniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'artikel';
        $slug = $base;
        $counter = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }

    // butuh php artisan storage:link
    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/'.$this->image) : null;
    }

    public function getExcerptAttribute(): string
    {
        return Str::limit(strip_tags($this->content), 140);
    }
}

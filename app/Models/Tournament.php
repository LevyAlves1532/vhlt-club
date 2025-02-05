<?php

namespace App\Models;

use App\Enum\TournamentStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Tournament extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'community_id',
        'title',
        'slug',
        'short_description',
        'description',
        'status',
        'number_players_team',
        'is_private',
    ];

    protected $casts = [
        'status' => TournamentStatusEnum::class,
    ];

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('small')
            ->fit(Fit::Contain, 400, 300);

        $this->addMediaConversion('medium')
            ->fit(Fit::Contain, 800, 600);

        $this->addMediaConversion('big')
            ->fit(Fit::Contain, 1200, 900);
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }
}

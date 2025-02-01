<?php

namespace App\Models;

use App\Enum\TournamentStatusEnum;
use Illuminate\Database\Eloquent\Model;
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
}

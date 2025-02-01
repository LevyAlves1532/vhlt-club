<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Community extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'slug',
        'is_active',
    ];

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('small')
            ->fit(Fit::Contain, 400, 400);

        $this->addMediaConversion('medium')
            ->fit(Fit::Contain, 800, 800);

        $this->addMediaConversion('big')
            ->fit(Fit::Contain, 1200, 1200);
    }
}

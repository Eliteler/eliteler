<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Background music for the analytics globe, managed in admin > Visitor Analytics.
 * file_url is a full URL or a "storage/..." path of an uploaded mp3.
 */
class AnalyticsMusicTrack extends Model
{
    protected $fillable = ['title', 'artist', 'file_url', 'status', 'sort_order'];

    protected $casts = [
        'status'     => 'boolean',
        'sort_order' => 'integer',
    ];

    public function isUpload(): bool
    {
        return str_starts_with($this->file_url, 'storage/');
    }

    /** Playable URL for the browser. */
    public function src(): string
    {
        return $this->isUpload() ? asset($this->file_url) : $this->file_url;
    }

    /** Tracks offered to customers, in play order. */
    public static function playlist(): array
    {
        return static::where('status', 1)->orderBy('sort_order')->orderBy('id')->get()
            ->map(fn (self $track) => ['id' => $track->id, 'title' => $track->title, 'artist' => $track->artist, 'src' => $track->src()])
            ->all();
    }
}

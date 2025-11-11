<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Rennokki\QueryCache\Traits\QueryCacheable;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, BelongsToMany};


class Advertising extends Model
{
    /** @use HasFactory<\Database\Factories\AdvertisingFactory> */
    use HasFactory, QueryCacheable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'content',
        'title',
        'description',
        'language',
        'published_at',
        'expires_at',
        'status',
        'telegram_post_id',
        'post_url',
        'link',
        'views',
        'reactions_count'
    ];  

    /**
     * Get the attributes that should be cast.
     *
     * @var list<array, string>
     */
    protected $casts = [
        'content' => 'array',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
        'views' => 'integer',
        'reactions_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Specify the amount of time to cache queries.
     * Do not specify or set it to null to disable caching.
     *
     * @var int|\DateTime
     */
    public $cacheFor = 3600; // cache time, in seconds

    /**
     * The cache driver to be used.
     *
     * @var string
     */
    public $cacheDriver = 'redis';


    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'advertising_tag')->withTimestamps();
    }
}

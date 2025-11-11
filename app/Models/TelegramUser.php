<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Rennokki\QueryCache\Traits\QueryCacheable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use SergiX44\Nutgram\Nutgram;

class TelegramUser extends Model
{
    /** @use HasFactory<\Database\Factories\TelegramUserFactory> */
    use HasFactory, QueryCacheable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'username',
        'first_name',
        'last_name',
        'phone',
        'language',
        'is_bot',
        'is_premium',
        'language_selected'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @var list<array, string>
     */
    protected $casts = [
        'user_id' => 'integer',
        'is_bot' => 'boolean',
        'language_selected' => 'boolean',
        'is_premium' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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

    /**
     * Invalidate the cache automatically
     * upon update in the database.
     *
     * @var bool
     */
    protected static $flushCacheOnUpdate = true;

    

    public function resumes(): HasMany
    {
        return $this->hasMany(Resume::class);
    }

    public function vacancies(): HasMany
    {
        return $this->hasMany(Vacancy::class);
    }

    public static function currentByBot(Nutgram $bot): ?self
    {
        return static::where('user_id', $bot->userId())->first();
    }

    public function lang()
    {
        return $this->language ?? 'ru';
    }
}

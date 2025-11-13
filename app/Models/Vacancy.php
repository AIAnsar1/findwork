<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Rennokki\QueryCache\Traits\QueryCacheable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Vacancy extends Model
{
    /** @use HasFactory<\Database\Factories\VacancyFactory> */
    use HasFactory, QueryCacheable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'company',
        'salary',
        'experience',
        'employment',
        'schedule',
        'work_hours',
        'format',
        'responsibilities',
        'auto_posting',
        'requirements',
        'conditions',
        'benefits',
        'contact_name',
        'contact_phone',
        'last_posted_at',
        'contact_email',
        'contact_telegram',
        'status',
        'position',
        'address',
        'telegram_user_id'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @var list<array, string>
     */
    protected $casts = [
        'salary' => 'integer',
        'auto_posting' => 'boolean',
        'created_at' => 'datetime',
        'last_posted_at' => 'datetime',
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

    public function telegramUser(): BelongsTo
    {
        return $this->belongsTo(TelegramUser::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'vacancy_tag')->withTimestamps();
    }
}

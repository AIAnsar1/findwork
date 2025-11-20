<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Rennokki\QueryCache\Traits\QueryCacheable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Resume extends Model
{
    /** @use HasFactory<\Database\Factories\ResumeFactory> */
    use HasFactory;


    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'full_name',
        'age',
        'address',
        'position',
        'salary',
        'employment',
        'schedule',
        'format',
        'experience_years',
        'skills',
        'last_posted_at',
        'work_experience',
        'auto_posting',
        'phone',
        'telegram',
        'status',
        'about',
        'telegram_user_id'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @var list<array, string>
     */
    protected $casts = [
        'age' => 'integer',
        'salary' => 'integer',
        'experience_years' => 'integer',
        'work_experience' => 'array',
        'last_posted_at' => 'datetime',
        'auto_posting' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];




    public function telegramUser(): BelongsTo
    {
        return $this->belongsTo(TelegramUser::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'resume_tag')->withTimestamps();
    }
}

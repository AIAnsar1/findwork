<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Rennokki\QueryCache\Traits\QueryCacheable;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, BelongsToMany, hasMany};

class Channel extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
        'channel_id',
        'comments_enabled',
        'invite_link',
        'bot_is_admin',
        'language',
        'members_count',
        'last_synced_at',
        'group_id'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @var list<array, string>
     */
    protected $casts = [
        'comments_enabled' => 'boolean',
        'bot_is_admin' => 'boolean',
        'members_count' => 'integer',
        'last_synced_at' => 'datetime'
    ];



    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class)->withTimestamps();;
    }


    public function advertisings(): hasMany
    {
        return $this->hasMany(Advertising::class);
    }
}

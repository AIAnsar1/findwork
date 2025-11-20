<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Rennokki\QueryCache\Traits\QueryCacheable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Group extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'username',
        'description',
        'group_id',
        'anti_spam_mode',
        'auto_ban_user',
        'ban_message',
        'banned_words',
        'banned_links',
        'banned_usernames',
        'max_warnings',
        'invite_link',
        'bot_is_admin',
        'language',
        'ban_on_link_username',
        'members_count',
        'last_synced_at'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @var list<array, string>
     */
    protected $casts = [
        'anti_spam_mode' => 'boolean',
        'auto_ban_user' => 'boolean',
        'bot_is_admin' => 'boolean',
        'ban_on_link_username' => 'boolean',
        'banned_words' => 'array',
        'banned_links' => 'array',
        'banned_usernames' => 'array',
        'last_synced_at' => 'datetime',
        'members_count' => 'integer'
    ];




    public function channel(): HasOne
    {
        return $this->hasOne(Channel::class);
    }

    public function advertisings(): BelongsToMany
    {
        return $this->belongsToMany(Advertising::class, 'advertising_group')->withTimestamps();
    }

}

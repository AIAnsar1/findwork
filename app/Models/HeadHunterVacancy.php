<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeadHunterVacancy extends Model
{
    use HasFactory;

    protected $fillable = [
        'hh_id',
        'external_id',
        'name',
        'description',
        'salary',
        'employer_name',
        'employer_info',
        'area_name',
        'experience',
        'employment',
        'schedule',
        'url',
        'hh_status',
        'last_checked_at',
        'check_attempts',
        'check_error',
        'auto_posting',
        'published_at',
        'is_approved',
        'is_published',
        'published_to_channel_at',
        'raw_data',
        'area_id',
    ];

    protected $casts = [
        'salary' => 'array',
        'employer_info' => 'array',
        'experience' => 'array',
        'employment' => 'array',
        'schedule' => 'array',
        'raw_data' => 'array',
        'published_at' => 'datetime',
        'last_checked_at' => 'datetime',
        'published_to_channel_at' => 'datetime',
        'is_approved' => 'boolean',
        'is_published' => 'boolean',
        'auto_posting' => 'boolean',
    ];

    public function scopeAutoposting($query)
    {
        return $query->where('auto_posting', true);
    }

    public function scopeNotPublished($query)
    {
        return $query->where('is_published', false);
    }

    public function scopeFromArea($query, $area)
    {
        return $query->where('area_name', $area);
    }

    public function scopeActive($q)
    {
        return $q->where('hh_status', 'active');
    }

    public function scopeClosed($q)
    {
        return $q->where('hh_status', 'closed');
    }

    public function scopeApproved($q)
    {
        return $q->where('is_approved', true);
    }

    public function scopeReadyForPublication($q)
    {
        return $q->where('is_approved', true)->where('is_published', false)->where('auto_posting', true);
    }

    public function scopeNeedsChecking($q)
    {
        return $q->where('last_checked_at', '<=', now()->subHours(6))->where('check_attempts', '<', 3);
    }

}

<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class User extends Authenticatable implements HasMedia
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'bio',
        'xp_total',
        'level_id',
        'streak_days',
        'last_active_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'xp_total' => 'integer',
            'level_id' => 'integer',
            'streak_days' => 'integer',
            'last_active_at' => 'datetime',
        ];
    }

    protected $appends = [
        'profile_photo',
        'completed',
        'is_following',
        'followers_count',
        'following_count'
    ];

    public function getCompletedAttribute()
    {
        if (!$this->getAttribute('name') || !$this->getAttribute('email') || !$this->getAttribute('username')) return false;
        else return true;
    }

    public function getProfilePhotoAttribute()
    {
        return count($this->getMedia('profile_photo')->toArray()) ? $this->getMedia('profile_photo')[0]->getFullUrl() : '';
    }

    public function socialLinks()
    {
        return $this->hasMany(UserSocialLink::class)->orderBy('position');
    }

    /**
     * Users that this user is following
     */
    public function following()
    {
        return $this->belongsToMany(User::class, 'followers', 'follower_id', 'following_id')
            ->withTimestamps();
    }

    /**
     * Users that are following this user
     */
    public function followers()
    {
        return $this->belongsToMany(User::class, 'followers', 'following_id', 'follower_id')
            ->withTimestamps();
    }

    /**
     * Check if the authenticated user is following this user
     */
    public function getIsFollowingAttribute()
    {
        if (!auth()->guard('api')->check()) {
            return false;
        }
        return $this->followers()->where('follower_id', auth()->guard('api')->id())->exists();
    }

    /**
     * Get followers count
     */
    public function getFollowersCountAttribute()
    {
        return $this->followers()->count();
    }

    /**
     * Get following count
     */
    public function getFollowingCountAttribute()
    {
        return $this->following()->count();
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function upvotes()
    {
        return $this->hasMany(Upvote::class);
    }

    public function projectFunds()
    {
        return $this->hasMany(ProjectFund::class);
    }

    public function githubToken()
    {
        return $this->hasOne(UserGitHubToken::class);
    }

    public function githubImports()
    {
        return $this->hasMany(GitHubImport::class);
    }

    /**
     * Get the user's current level.
     */
    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    /**
     * Get the user's XP logs.
     */
    public function xpLogs()
    {
        return $this->hasMany(XpLog::class);
    }

    /**
     * Get the user's badges.
     */
    public function badges()
    {
        return $this->belongsToMany(Badge::class, 'user_badges')
            ->withPivot('awarded_at')
            ->withTimestamps();
    }

    /**
     * Get the user's tasks.
     */
    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'user_tasks')
            ->withPivot('status', 'completed_at', 'assigned_at')
            ->withTimestamps();
    }

    /**
     * Get the user's questions.
     */
    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Get the user's answers.
     */
    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
}

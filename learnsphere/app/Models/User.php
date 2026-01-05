<?php


namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

// use App\Models\Role;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_approved',
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
            'is_approved' => 'boolean',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn($word) => Str::substr($word, 0, 1))
            ->implode('');
    }


    // public function roles()
    // {
    //     return $this->belongsToMany(Role::class);
    // }

    // public function hasRole($roleName)
    // {
    //     return $this->roles()->where('name', $roleName)->exists();
    // }

    // public function assignRole($roleName)
    // {
    //     $role = Role::firstOrCreate(['name' => $roleName]);
    //     if(!$this->roles->contains($role->id)){
    //         $this->roles()->attach($role);
    //     }
    // }
    public function courses() // instructor's courses
    {
        return $this->hasMany(Course::class, 'instructor_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function enrolledCourses()
    {

        return $this->belongsToMany(Course::class, 'enrollments');

    }



    public function completedLessons()
    {
        return $this->belongsToMany(Lesson::class, 'lesson_user');
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }
}

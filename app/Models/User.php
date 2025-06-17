<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\VerifyRegisterEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens; //add the namespace

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable; //use it here

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'role',
        'email',
        'password',
        'account_status',
        'email_verified_at',
        'remember_token'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function vendor()
    {
        return $this->hasOne(Vendor::class);
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    //package relationship
    public function packages()
    {
        return $this->hasMany(Package::class);
    }

    //driver relationship
    public function driver()
    {
        return $this->hasOne(Driver::class);
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyRegisterEmail);
    }
}

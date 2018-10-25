<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Activitylog\Traits\LogsActivity;

class Admin extends Authenticatable
{
    protected $table = 'admins';
    public $timestamps = true;

    use SoftDeletes;
    use Notifiable,LogsActivity,HasApiTokens;


    public $modelPath = 'App\Models\Admin';
    protected $dates = ['lastlogin','created_at','updated_at','deleted_at'];
    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'mobile',
        'avatar',
        'gender',
        'birthdate',
        'address',
        'password',
        'remember_token',
        'description',
        'job_title',
        'status',
        'lastlogin',
        'language_key',
    ];
    protected $hidden = array('password', 'remember_token');

    /*
     * Log Activity
     */
    protected static $logAttributes = [
        'email',
        'mobile',
        'password',
        'job_title',
        'status',
    ];
    public function getFullNameAttribute()
    {
        return $this->first_name.' '.$this->last_name;
    }
}

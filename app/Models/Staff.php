<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{

use SoftDeletes;
    public $timestamps = true;
    protected $dates = ['created_at','updated_at','deleted_at'];
    protected $fillable  = [
        'first_name',
        'last_name',
        'email',
        'job',
        'salary',
        'created_by'
    ];

    /**
     * @return string
     */
    public function getFullNameAttribute()
    {
        return $this->first_name.' '.$this->last_name;
    }

public function createdBy(){
        return $this->belongsTo('App\Models\Admin','created_by');
}
}

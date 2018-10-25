<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bonus extends Model
{

    use LogsActivity;
    public $timestamps = true;
    public $table = 'bonuses';
    protected $dates = ['created_at', 'updated_at'];
    protected $fillable = [
        'staff_id',
        'year',
        'month',
        'day',
        'amount',
        'bonus_rate',
        'created_by'
    ];

    /**
     * @return string
     */

    protected static $logAttributes = [
        'staff_id',
        'amount',
        'created_by',
    ];

    public function staff()
    {
        return $this->belongsTo('App\Models\Staff', 'staff_id');
    }

    public function createdBy()
    {
        return $this->belongsTo('App\Models\Admin', 'created_by');
    }
}

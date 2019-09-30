<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

    ];

    public function getContentAttribute($value)
    {
        if ($this->type == 2) {
            return env('APP_URL', 'http://www.service.xitou.online') . '/storage/' . $value;
        }
        return $value;
    }


}

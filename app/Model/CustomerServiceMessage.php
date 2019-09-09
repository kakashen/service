<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CustomerServiceMessage extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'customer_service_id', 'message'
    ];



}

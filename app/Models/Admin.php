<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'role_id', 'username', 'email', 'password', 'first_name', 'last_name', 'image', 'status','is_waiter','is_casher','is_delivery'
    ];


    public function role() {
      return $this->belongsTo('App\Models\Role');
    }
    public function waiter() {
      return $this->belongsTo('App\Models\Waiter');
    }

}

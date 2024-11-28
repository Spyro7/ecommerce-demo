<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $guarded = false;

    public function orders(){
        return $this->belongsTo(Order::class);
    }

    public function getFullNameAttribute(){
        return "{$this->first_name} {$this->last_name}";
    }
}

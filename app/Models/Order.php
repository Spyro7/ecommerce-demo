<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = false;

    public function users(){
        return $this->belongsTo(User::class);
    }
    public function items(){
        return $this->hasMany(OrderItem::class);
    }
    public function addresses(){
        return $this->hasOne(Address::class);
    }
}

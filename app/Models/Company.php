<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'phone', 'address'];

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function warehouses()
    {
        return $this->hasMany(Warehouse::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}

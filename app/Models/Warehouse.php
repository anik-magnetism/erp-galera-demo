<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = ['company_id', 'name', 'location'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
}

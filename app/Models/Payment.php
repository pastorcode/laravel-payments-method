<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = ['paymentId', 'userId', 'productId', 'price' ,'paymentMethod', 'statusDate', 'status', 'created_at', 'updated_at'];
}

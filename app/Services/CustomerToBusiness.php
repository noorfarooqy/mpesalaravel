<?php

namespace Noorfarooqy\MpesaLaravel\Services;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerToBusiness extends Model
{

    use HasFactory;

    public $guarded = [];
    protected $table = "mp_customer_to_business";
}

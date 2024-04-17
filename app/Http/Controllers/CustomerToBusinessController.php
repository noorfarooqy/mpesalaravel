<?php

namespace Noorfarooqy\Http\Controllers;

use Illuminate\Http\Request;
use Noorfarooqy\MpesaLaravel\Controllers\Controller;
use Noorfarooqy\MpesaLaravel\Services\CustomerToBusinessServices;

class CustomerToBusinessController extends Controller
{

    public function ValidateTransaction(Request $request, CustomerToBusinessServices $customerToBusinessServices)
    {
        return $customerToBusinessServices->validateCustomerToBusiness($request);
    }

    public function ConfirmTransaction(Request $request, CustomerToBusinessServices $customerToBusinessServices)
    {
        return $customerToBusinessServices->confirmCustomerToBusiness($request);
    }

}

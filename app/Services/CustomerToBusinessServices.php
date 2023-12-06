<?php

namespace Noorfarooqy\MpesaLaravel\Services;

use Noorfarooqy\MpesaLaravel\Helpers\HasCustomerToBusiness;
use Noorfarooqy\NoorAuth\Services\NoorServices;

class CustomerToBusinessServices extends NoorServices implements CustomerToBusinessContract
{
    use HasCustomerToBusiness;
}

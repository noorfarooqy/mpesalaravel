<?php

namespace Noorfarooqy\MpesaLaravel\Services;

interface CustomerToBusinessContract
{

    public function extraValidation();
    public function extraPostValidation();
    public function extraAccountValidation();
    public function extraAccountConfirmation();
    public function extraPostConfirmation();
}

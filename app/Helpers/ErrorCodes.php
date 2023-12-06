<?php

namespace Noorfarooqy\MpesaLaravel\Helpers;

class ErrorCodes
{

    static $NO_ERROR_CODE = '0';
    static $NO_ERROR_DESC = "Service processing successful";

    static $PARSE_ERROR_CODE = '1';
    static $PARSE_ERROR_DESC = "Soup parse processing failed";

    static $INVALID_ACCOUNT_CODE = '2';
    static $INVALID_ACCOUNT_DESC = "The provided account number for c2b transaction is invalid";

    static $NON_EXISTANT_ACCOUNT_CODE = '2';
    static $NON_EXISTANT_ACCOUNT_DESC = "The provided account number for c2b transaction does not exist";


    static $FAILED_TO_SAVE_CODE = '3';
    static $FAILED_TO_SAVE_DESC = "Failed to save the transaction information for validation";


    static $UNVALIDATED_CONFIRMATION_ERROR_CODE = '4';
    static $UNVALIDATED_CONFIRMATION_ERROR_DESC = "Transaction is not valided. Confirmation failed";


    static $DUPLICATED_CONFIRMATION_CODE = '4';
    static $DUPLICATED_CONFIRMATION_DESC = "Duplicate transaction confirmation provided. ";
}

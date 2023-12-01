<?php

namespace Noorfarooqy\MpesaLaravel\Helpers;

enum ErrorCodes: string
{

    case NO_ERROR_CODE = '0';
    case NO_ERROR_DESC = "Service processing successful";

    case PARSE_ERROR_CODE = '1';
    case PARSE_ERROR_DESC = "Soup parse processing failed";

    case INVALID_ACCOUNT_CODE = '2';
    case INVALID_ACCOUNT_DESC = "The provided account number for c2b transaction is invalid";

    case NON_EXISTANT_ACCOUNT_CODE = '2';
    case NON_EXISTANT_ACCOUNT_DESC = "The provided account number for c2b transaction does not exist";


    case FAILED_TO_SAVE_CODE = '3';
    case FAILED_TO_SAVE_DESC = "Failed to save the transaction information for validation";
}

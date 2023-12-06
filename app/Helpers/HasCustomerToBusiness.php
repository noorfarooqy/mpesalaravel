<?php

namespace Noorfarooqy\MpesaLaravel\Helpers;

use Illuminate\Support\Facades\Log;
use Noorfarooqy\MpesaLaravel\Helpers\ErrorCodes;
use Noorfarooqy\MpesaLaravel\Jobs\SendEasyNotificationSmsJob;
use Noorfarooqy\MpesaLaravel\Services\CustomerAccount;
use Noorfarooqy\MpesaLaravel\Services\CustomerToBusiness;
use Noorfarooqy\MpesaLaravel\Services\CustomerToBusinessContract;

trait HasCustomerToBusiness
{

    protected $xmlParser;
    public $third_party_id;
    public $transaction;

    public $validated_transaction;

    public function validateCustomerToBusiness($request)
    {
        $this->request = $request;

        // $raw_xml = file_get_contents("php://input");
        // $this->xmlParser = new MpesaXmlParser();
        // $parsed_xml = $this->xmlParser->parse($raw_xml);
        $parsed_xml = $request->parsed_xml;
        $this->third_pary_id = 'MPC2B_' . gmdate('YmdHis');

        $error_code = ErrorCodes::$NO_ERROR_CODE;
        $error_message = ErrorCodes::$NO_ERROR_DESC;

        if (!$parsed_xml) {
            $error_code = ErrorCodes::$PARSE_ERROR_CODE; //other error
            $error_message = ErrorCodes::$PARSE_ERROR_DESC;
            return $this->ctobValidationResponse($this->third_pary_id, $error_code, $error_message . $parsed_xml);
        }

        $transaction = $parsed_xml->{'Body'}->{'C2BPaymentValidationRequest'};

        $account = null;
        $account_lengths = config('mpesalaravel.ctob.account_length', [10]);
        if (!in_array(strlen($transaction->{'BillRefNumber'}), $account_lengths)) {
            $sms_message = "Dear client, the account number you've entered is not a valid account number. Kindly ensure the account number and retry";
            $error_code = ErrorCodes::$INVALID_ACCOUNT_CODE;
            $error_message = ErrorCodes::$INVALID_ACCOUNT_DESC;
            SendEasyNotificationSmsJob::dispatch((string)$transaction->{'MSISDN'}, $sms_message . " - $account ");
            return $this->ctobValidationResponse($this->third_pary_id, $error_code, $error_message . " - $account ");
        }
        $this->transaction = $transaction;
        $account = $this->extraAccountValidation();
        if (!$account) {
            $error_code = ErrorCodes::$NON_EXISTANT_ACCOUNT_CODE;;
            $error_message = ErrorCodes::$INVALID_ACCOUNT_DESC;
            $account = $transaction->{'BillRefNumber'};
            $sms_message = "Dear client, the account number $account or name you've entered does not exist. Kindly ensure the account number and retry";
            SendEasyNotificationSmsJob::dispatch((string)$transaction->{'MSISDN'}, $sms_message);
            // $account = null;
            return $this->ctobValidationResponse($this->third_pary_id, $error_code, $error_message . " - $account ");
        }
        $validation_data = [
            'customer_account' => $account?->id,
            'trn_party_trn_id' => $this->third_pary_id,
            'trn_type' => $transaction->{'TransType'},
            'trn_id' => $transaction->{'TransID'},
            'trn_time' => $transaction->{'TransTime'},
            'trn_amount' => $transaction->{'TransAmount'},
            'trn_bill_ref' => $transaction->{'BillRefNumber'},
            'trn_invoice_number' => $transaction->{'InvoiceNumber'},
            'trn_msisdn' => $transaction->{'MSISDN'},
            'trn_kyc_fn' => $transaction->{'KYCInfo'}[0]?->{'KYCValue'},
            'trn_kyc_mn' => $transaction->{'KYCInfo'}[1]?->{'KYCValue'},
            'trn_kyc_ln' => $transaction->{'KYCInfo'}[2]?->{'KYCValue'},
            'val_error' => $error_message,
            'val_error_code' => $error_code,
            'is_validated' => $error_code == '0',
            'validated_at' => $error_code == '0' ? now() : null,
        ];

        try {
            $this->validated_transaction = CustomerToBusiness::create($validation_data);
            $this->extraPostValidation();
        } catch (\Throwable $th) {
            $this->setError($th->getMessage());
            $error_code = ErrorCodes::$FAILED_TO_SAVE_CODE;
            $error_message = ErrorCodes::$FAILED_TO_SAVE_DESC . (env('APP_DEBUG') ? $th->getMessage() : '');
        }

        return $this->ctobValidationResponse($this->third_pary_id, $error_code, $error_message);
    }

    public function ConfirmCustomerToBusiness($request)
    {
        $parsed_xml = $request->parsed_xml;
        if (!$parsed_xml) {
            $error_message = 'Soup XML Failed';
            return $this->ctobConfirmationResponse('SMFB_C2B_ERROR_' . gmdate('YmdHis', time()), $error_message);
        }
        $transaction = $parsed_xml->{'Body'}->{'C2BPaymentConfirmationRequest'};

        $transaction_details['error_code'] = ErrorCodes::$NO_ERROR_CODE;
        $transaction_details['transaction_type'] = $transaction->{'TransactionType'};
        $transaction_details['transaction_id'] = $transaction->{'TransID'};
        $transaction_details['transaction_time'] = $transaction->{'TransTime'};
        $transaction_details['transaction_amount'] = $transaction->{'TransAmount'};
        $transaction_details['transaction_short_code'] = $transaction->{'BusinessShortCode'};
        $transaction_details['transaction_bill_ref_number'] = $transaction->{'BillRefNumber'};
        $transaction_details['transaction_invoice_number'] = $transaction->{'InvoiceNumber'};
        $transaction_details['transaction_msisdn'] = $transaction->{'MSISDN'};
        $transaction_details['transaction_party_id'] = $transaction->{'ThirdPartyTransID'};
        $transaction_details['transaction_org_balance'] = $transaction->{'OrgAccountBalance'};
        $transaction_details['kyc_details'] = $transaction->{'KYCInfo'};

        $this->transaction = $transaction;
        $account = $this->extraAccountConfirmation();
        if (!$account) {
            $error_message = ErrorCodes::$INVALID_ACCOUNT_DESC;
            $sms_message = "Dear client, the account number or name you've entered can not be confirmed. Kindly retry or contact support.";
            SendEasyNotificationSmsJob::dispatch((string)$transaction->{'MSISDN'}, $sms_message);
        }

        $transaction = CustomerToBusiness::where([
            ['trn_party_trn_id', $transaction_details['transaction_party_id']],
            ['trn_id', $transaction_details['transaction_id']],
            ['trn_bill_ref', $transaction_details['transaction_bill_ref_number']],
        ])->get()->first();
        if (!$transaction) {
            $error_message = ErrorCodes::$UNVALIDATED_CONFIRMATION_ERROR_DESC;
            return $this->ctobConfirmationResponse($transaction_details['transaction_party_id'], $error_message);
        }
        if ($transaction?->is_confirmed == 1 || $transaction?->confirmed_at != null) {
            $error_message = ErrorCodes::$DUPLICATED_CONFIRMATION_DESC;
            return $this->ctobConfirmationResponse($transaction_details['transaction_party_id'], $error_message);
        }
        $full_name = $transaction_details['kyc_details'][0]->{'KYCValue'} . ' ' . $transaction_details['kyc_details'][1]->{'KYCValue'} . ' ' . $transaction_details['kyc_details'][2]->{'KYCValue'};

        $transaction->is_confirmed = true;
        $transaction->confirmed_at = now();
        $transaction->save();


        $this->validated_transaction = $transaction;;
        $this->extraPostConfirmation();
        return $this->ctobConfirmationResponse($transaction_details['transaction_party_id']);
    }


    public function ctobValidationResponse($transaction_id = 'SMFB_DEFAULT', $code, $message = 'Service processing successful')
    {
        $response = "<soapenv:Envelope xmlns:soapenv='http://schemas.xmlsoap.org/soap/envelope/'
            xmlns:c2b='http://cps.huawei.com/cpsinterface/c2bpayment'>
            <soapenv:Header/>
            <soapenv:Body xmlns:ns1='http://cps.huawei.com/cpsinterface/c2bpayment'>
                <ns1:C2BPaymentValidationResult>
                    <ResultCode>$code</ResultCode>
                    <ResultDesc>$message</ResultDesc>
                    <ThirdPartyTransID>$transaction_id</ThirdPartyTransID>
                </ns1:C2BPaymentValidationResult>
            </soapenv:Body>
        </soapenv:Envelope>";
        if ($code != 0) {
            Log::debug($message);
        }

        return $response;
    }
    public function ctobConfirmationResponse($transaction_id = 'SMFB_DEFAULT', $message = 'result received.')
    {
        $response = "<soapenv:Envelope xmlns:soapenv='http://schemas.xmlsoap.org/soap/envelope/'
            xmlns:c2b='http://cps.huawei.com/cpsinterface/c2bpayment'>
            <soapenv:Header/>
            <soapenv:Body>
                <ns1:C2BPaymentConfirmationResult> C2B Payment Transaction $transaction_id $message </ns1:C2BPaymentConfirmationResult>
            </soapenv:Body>
        </soapenv:Envelope>";
        if ($message != 'result received.') {
            Log::debug($response);
        }
        return $response;
    }


    public function extraValidation()
    {
        return true;
    }

    public function extraPostValidation()
    {
        return true;
    }

    public function extraAccountValidation()
    {
        Log::info('Default validation');
        $account = CustomerAccount::where('account_number', $this->transaction->{'BillRefNumber'})
            ->orWhere('short_name', $this->transaction->{'BillRefNumber'})->get()->first();

        if (!$account && config('mpesalaravel.ctob.allow_new_accounts')) {
            $account = CustomerAccount::create([
                'account_number' => $this->transaction->{'BillRefNumber'},
                'account_name' => $this->transaction->{'KYCInfo'}[0]->{'KYCvalue'},
                'short_name' => $this->transaction->{'BillRefNumber'},
            ]);
        }
        return $account;
    }
    public function extraAccountConfirmation()
    {
        $account = CustomerAccount::where('account_number', $this->transaction->{'BillRefNumber'})
            ->orWhere('short_name', $this->transaction->{'BillRefNumber'})->get()->first();
        return $account;
    }

    public function extraPostConfirmation()
    {
        return true;
    }
}

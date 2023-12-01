<?php

namespace Noorfarooqy\Services;

use Noorfarooqy\EasyNotifications\Jobs\SendEasyNotificationSmsJob;
use Noorfarooqy\Middleware\MpesaXmlParser;
use Noorfarooqy\MpesaLaravel\Helpers\ErrorCodes;
use Noorfarooqy\MpesaLaravel\Services\CustomerAccount;
use Noorfarooqy\MpesaLaravel\Services\CustomerToBusiness;
use Noorfarooqy\NoorAuth\Services\NoorServices;

class CustomerToBusinessServices extends NoorServices
{

    protected $xmlParser;
    public function __construct()
    {
        $this->xmlParser = new MpesaXmlParser();
    }

    public function validateCustomerToBusiness($request)
    {
        $this->request = $request;

        $raw_xml = file_get_contents("php://input");
        $parsed_xml = $this->xmlParser->parse($raw_xml);
        $third_pary_id = 'MPC2B_' . gmdate('YmdHis');

        $error_code = ErrorCodes::NO_ERROR_CODE;
        $error_message = ErrorCodes::NO_ERROR_DESC;

        if (!$parsed_xml) {
            $error_code = ErrorCodes::PARSE_ERROR_CODE; //other error
            $error_message = ErrorCodes::PARSE_ERROR_DESC;
            return $this->mpesaResponse($third_pary_id, $error_code, $error_message);
        }

        $transaction = $parsed_xml->{'Body'}->{'C2BPaymentValidationRequest'};

        $account = null;
        $account_lengths = config('mpesalaravel.ctob.account_length', [10]);
        if (!in_array(strlen($transaction->{'BillRefNumber'}), $account_lengths)) {
            $sms_message = "Dear client, the account number you've entered is not a valid account number. Kindly ensure the account number and retry";
            $error_code = ErrorCodes::INVALID_ACCOUNT_CODE;
            $error_message = ErrorCodes::INVALID_ACCOUNT_DESC;
            SendEasyNotificationSmsJob::dispatch((string)$transaction->{'MSISDN'}, $sms_message);
        } else {
            $account = CustomerAccount::where('account_number', $transaction->{'BillRefNumber'})
                ->orWhere('short_name', $transaction->{'BillRefNumber'})->get()->first();
            if (!$account) {
                $error_code = ErrorCodes::NON_EXISTANT_ACCOUNT_CODE;;
                $error_message = ErrorCodes::INVALID_ACCOUNT_DESC;
                $sms_message = "Dear client, the account number or name you've entered does not exist. Kindly ensure the account number and retry";
                SendEasyNotificationSmsJob::dispatch((string)$transaction->{'MSISDN'}, $sms_message);
                $account = null;
            }
            $transaction->{'BillRefNumber'} = $account->account_number;
        }

        $validation_data = [
            'customer_account' => $account->id,
            'trn_party_trn_id' => $third_pary_id,
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
            $created_transaction = CustomerToBusiness::create($validation_data);
        } catch (\Throwable $th) {
            $this->setError($th->getMessage());
            $error_code = ErrorCodes::FAILED_TO_SAVE_CODE;
            $error_message = ErrorCodes::FAILED_TO_SAVE_DESC;
        }


        return $this->mpesaResponse($third_pary_id, $error_code, $error_message);
    }


    public function mpesaResponse($transaction_id = 'SMFB_DEFAULT', $code, $message = 'Service processing successful')
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
            // LogMpesaFailedRequests::dispatch(json_encode([$transaction_id, $code]), $message . '--6');
        }
        // Log::info($response);

        return $response;

        // Log::info($response);
        // return new SimpleXMLElement((string) $response);
    }
}

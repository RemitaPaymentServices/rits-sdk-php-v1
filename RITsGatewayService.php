<?php

// Define a class
include 'Config/Credentials.php';
include 'Constants/ApplicationUrl.php';
include 'Request/AccountEnquiry/AccountEnquiryRequest.php';
include 'Request/AddAccount/AddAccountRequest.php';
include 'Request/PaymentSingle/PaymentSingleRequest.php';
include 'Request/PaymentBulk/PaymentBulkRequest.php';
include 'Request/PaymentBulk/BulkPaymentInfo.php';
include 'Request/PaymentBulk/PaymentDetails.php';
include 'Request/ValidateAccountOTP/AuthParams.php';
include 'Request/ValidateAccountOTP/ValidateAccountOTPRequest.php';
include 'Util/AES128CBC.php';
include 'Util/HTTPUtil.php';

class RITsGatewayService
{

    public static $credentials;

    // INITIALIZE CREDENTIALS
    public static function init($initCredentials)
    {
        if (is_null($initCredentials)) {
            echo 'Credentials must be initialized';
            return;
        }

        $headers = array(
            'Content-Type: application/json',
            'MERCHANT_ID:' . $initCredentials->merchantId,
            'API_KEY:' . $initCredentials->apiKey
        );

        $initCredentials->headers = $headers;

        @RITsGatewayService::$credentials = $initCredentials;
    }

    // GET HEADERS
    public static function getHeaders()
    {
        $requestId = round(microtime(true) * 1000);
        $headers = RITsGatewayService::$credentials->headers;
        $apiKey = RITsGatewayService::$credentials->apiKey;
        $apiToken = RITsGatewayService::$credentials->apiToken;

        $time = date("H:i:s+000000");
        $date = date("Y-m-d");
        $timeStamp = $date . "T" . $time; // 2019-09-11T05:33:39+000000
        $apiHash = hash('sha512', $apiKey . $requestId . $apiToken);
        array_push($headers, 'REQUEST_ID:' . $requestId, 'API_DETAILS_HASH:' . $apiHash, 'REQUEST_TS:' . $timeStamp);
        return $headers;
    }

    // GET ACTIVE BANKS
    public static function activeBanks()
    {
        $url = RITsGatewayService::$credentials->url . ApplicationUrl::$activeBanks;
        $result = HTTPUtil::postMethod($url, RITsGatewayService::getHeaders());
        return json_decode($result, ActiveBanksResponse::class);
    }

    // ACCOUNT INQUIRY
    public static function accountInquiry($accountEnquiry)
    {
        $url = RITsGatewayService::$credentials->url . ApplicationUrl::$accountInquiry;

        $key = utf8_encode(RITsGatewayService::$credentials->key);
        $iv = utf8_encode(RITsGatewayService::$credentials->iv);

        // ENCODING DATA
        $accountNumber = utf8_encode($accountEnquiry->accountNo);
        $bankCode = utf8_encode($accountEnquiry->bankCode);

        // ENCRYPTING DATA
        $accountno_encrypted = AES128CBC::encrypt($accountNumber, $iv, $key);
        $bankcode_encrypted = AES128CBC::encrypt($bankCode, $iv, $key);

        $accountEnquiry->accountNo = $accountno_encrypted;
        $accountEnquiry->bankCode = $bankcode_encrypted;

        // POST BODY
        $phpArray = array(
            'accountNo' => $accountEnquiry->accountNo,
            'bankCode' => $accountEnquiry->bankCode
        );

        // POST CALL
        $result = HTTPUtil::postMethod($url, RITsGatewayService::getHeaders(), json_encode($phpArray));
        return json_decode($result, AccountEnquiryResponse::class);
    }

    // ADD ACCOUNT
    public static function addAccount($addAccount)
    {
        $url = RITsGatewayService::$credentials->url . ApplicationUrl::$addAccount;

        $key = utf8_encode(RITsGatewayService::$credentials->key);
        $iv = utf8_encode(RITsGatewayService::$credentials->iv);

        // ENCODING DATA
        $accountNumber = utf8_encode($addAccount->accountNo);
        $bankCode = utf8_encode($addAccount->bankCode);

        // ENCRYPTING DATA
        $accountno_encrypted = AES128CBC::encrypt($accountNumber, $iv, $key);
        $bankcode_encrypted = AES128CBC::encrypt($bankCode, $iv, $key);

        $addAccount->accountNo = $accountno_encrypted;
        $addAccount->bankCode = $bankcode_encrypted;

        // POST BODY
        $phpArray = array(
            'accountNo' => $addAccount->accountNo,
            'bankCode' => $addAccount->bankCode
        );

        // POST CALL
        $result = HTTPUtil::postMethod($url, RITsGatewayService::getHeaders(), json_encode($phpArray));
        return json_decode($result, AddAccountResponse::class);
    }

    // PAYMENT STATUS SINGLE
    public static function paymentStatusSingle($transRef)
    {
        $url = RITsGatewayService::$credentials->url . ApplicationUrl::$paymentStatusSingle;

        $key = utf8_encode(RITsGatewayService::$credentials->key);
        $iv = utf8_encode(RITsGatewayService::$credentials->iv);

        $transRef_encrypted = AES128CBC::encrypt($transRef, $iv, $key);

        // POST BODY
        $phpArray = array(
            'transRef' => $transRef_encrypted
        );

        // POST CALL
        $result = HTTPUtil::postMethod($url, RITsGatewayService::getHeaders(), json_encode($phpArray));
        return json_decode($result, PaymentStatusSingleResponse::class);
    }

    // PAYMENT STATUS BULK
    public static function paymentStatusBulk($batchRef)
    {
        $url = RITsGatewayService::$credentials->url . ApplicationUrl::$paymentStatusBulk;

        $key = utf8_encode(RITsGatewayService::$credentials->key);
        $iv = utf8_encode(RITsGatewayService::$credentials->iv);

        $batchRef_encrypted = AES128CBC::encrypt($batchRef, $iv, $key);

        // POST BODY
        $phpArray = array(
            'batchRef' => $batchRef_encrypted
        );

        // POST CALL
        $result = HTTPUtil::postMethod($url, RITsGatewayService::getHeaders(), json_encode($phpArray));
        return json_decode($result, PaymentStatusBulkResponse::class);
    }

    // PAYMENT SINGLE
    public static function singlePayment($paymentSingleRequest)
    {
        $url = RITsGatewayService::$credentials->url . ApplicationUrl::$paymentSingle;

        $key = utf8_encode(RITsGatewayService::$credentials->key);
        $iv = utf8_encode(RITsGatewayService::$credentials->iv);

        $encrypted_fromBank = AES128CBC::encrypt($paymentSingleRequest->fromBank, $iv, $key);
        $encrypted_debitAccount = AES128CBC::encrypt($paymentSingleRequest->debitAccount, $iv, $key);
        $encrypted_toBank = AES128CBC::encrypt($paymentSingleRequest->toBank, $iv, $key);
        $encrypted_creditAccount = AES128CBC::encrypt($paymentSingleRequest->creditAccount, $iv, $key);
        $encrypted_narration = AES128CBC::encrypt($paymentSingleRequest->narration, $iv, $key);
        $encrypted_amount = AES128CBC::encrypt($paymentSingleRequest->amount, $iv, $key);
        $encrypted_beneficiaryEmail = AES128CBC::encrypt($paymentSingleRequest->beneficiaryEmail, $iv, $key);
        $encrypted_transRef = AES128CBC::encrypt(rand(), $iv, $key);

        // POST BODY
        $phpArray = array(
            'toBank' => $encrypted_toBank,
            'creditAccount' => $encrypted_creditAccount,
            'narration' => $encrypted_narration,
            'amount' => $encrypted_amount,
            'transRef' => $encrypted_transRef,
            'fromBank' => $encrypted_fromBank,
            'debitAccount' => $encrypted_debitAccount,
            'beneficiaryEmail' => $encrypted_beneficiaryEmail
        );

        // POST CALL
        $result = HTTPUtil::postMethod($url, RITsGatewayService::getHeaders(), json_encode($phpArray));

        return json_decode($result, PaymentSingleResponse::class);
    }

    // PAYMENT BULK
    public static function bulkPayment($paymentBulkRequest)
    {
        $url = RITsGatewayService::$credentials->url . ApplicationUrl::$paymentBulk;

        $key = utf8_encode(RITsGatewayService::$credentials->key);
        $iv = utf8_encode(RITsGatewayService::$credentials->iv);

        $bulkPaymentInfo = $paymentBulkRequest->bulkPaymentInfo;

        $encrypted_batchRef = AES128CBC::encrypt($bulkPaymentInfo->batchRef, $iv, $key);
        $encrypted_totalAmount = AES128CBC::encrypt($bulkPaymentInfo->totalAmount, $iv, $key);
        $encrypted_debitAccount = AES128CBC::encrypt($bulkPaymentInfo->debitAccount, $iv, $key);
        $encrypted_narration = AES128CBC::encrypt($bulkPaymentInfo->narration, $iv, $key);
        $encrypted_bankCode = AES128CBC::encrypt($bulkPaymentInfo->bankCode, $iv, $key);

        $encrypted_paymentBulkRequest = new PaymentBulkRequest();

        $encrypted_bulkPaymentInfo = new BulkPaymentInfo();
        $encrypted_bulkPaymentInfo->totalAmount = $encrypted_totalAmount;
        $encrypted_bulkPaymentInfo->batchRef = $encrypted_batchRef;
        $encrypted_bulkPaymentInfo->debitAccount = $encrypted_debitAccount;
        $encrypted_bulkPaymentInfo->narration = $encrypted_narration;
        $encrypted_bulkPaymentInfo->bankCode = $encrypted_bankCode;

        $encrypted_paymentBulkRequest->bulkPaymentInfo = $encrypted_bulkPaymentInfo;

        $paymentDetails = $paymentBulkRequest->paymentDetails;

        $data_array = array();

        foreach ($paymentDetails as $value) {

            $encrypted_transRef = AES128CBC::encrypt($value->transRef, $iv, $key);

            $encrypted_narration = AES128CBC::encrypt($value->narration, $iv, $key);

            $encrypted_amount = AES128CBC::encrypt($value->amount, $iv, $key);

            $encrypted_benficiaryEmail = AES128CBC::encrypt($value->benficiaryEmail, $iv, $key);

            $encrypted_benficiaryBankCode = AES128CBC::encrypt($value->benficiaryBankCode, $iv, $key);

            $encrypted_benficiaryAccountNumber = AES128CBC::encrypt($value->benficiaryAccountNumber, $iv, $key);

            $data_array[] = array(
                'transRef' => $encrypted_transRef,
                'narration' => $encrypted_narration,
                'amount' => $encrypted_amount,
                'benficiaryEmail' => $encrypted_benficiaryEmail,
                'benficiaryBankCode' => $encrypted_benficiaryBankCode,
                'benficiaryAccountNumber' => $encrypted_benficiaryAccountNumber
            );
        }

        $encrypted_paymentBulkRequest->setPaymentDetails($data_array);

        // POST BODY
        $phpArray = array(
            'bulkPaymentInfo' => $encrypted_paymentBulkRequest->bulkPaymentInfo,
            'paymentDetails' => $encrypted_paymentBulkRequest->paymentDetails
        );

        // // POST CALL
        $result = HTTPUtil::postMethod($url, RITsGatewayService::getHeaders(), json_encode($phpArray));

        return json_decode($result, PaymentBulkResponse::class);
    }

    // VALIDATE ACCOUNT OTP
    public static function validateAccountOTP($validateAccountOTPRequest)
    {
        $url = RITsGatewayService::$credentials->url . ApplicationUrl::$validateAccountOTP;

        // POST CALL
        $result = HTTPUtil::postMethod($url, RITsGatewayService::getHeaders(), json_encode($validateAccountOTPRequest));
        return json_decode($result, ValidateAccountOTPResponse::class);
    }
}

?>


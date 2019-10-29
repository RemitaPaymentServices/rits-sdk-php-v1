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

        @RITsGatewayService::$credentials = $initCredentials;
    }

    // GET ACTIVE BANKS
    public static function activeBanks()
    {
        $url = RITsGatewayService::$credentials->url . ApplicationUrl::$activeBanks;

        echo 'URL: ', $url;
        echo "\n";
        echo 'HEADERS: ', json_encode(RITsGatewayService::$credentials->headers);
        echo "\n";

        $result = HTTPUtil::postMethod($url, RITsGatewayService::$credentials->headers);

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

        echo "\n\n\n";
        echo 'URL: ', $url;
        echo "\n";
        echo 'HEADERS: ', json_encode(RITsGatewayService::$credentials->headers);
        echo "\n";

        $accountEnquiry->accountNo = $accountno_encrypted;
        $accountEnquiry->bankCode = $bankcode_encrypted;

        echo "\n";
        echo '$accountno_encrypted: ', $accountEnquiry->accountNo;
        echo "\n";
        echo '$bankcode_encrypted: ', $accountEnquiry->bankCode;
        echo "\n";

        // POST BODY
        $phpArray = array(
            'accountNo' => $accountEnquiry->accountNo,
            'bankCode' => $accountEnquiry->bankCode
        );

        // POST CALL
        $result = HTTPUtil::postMethod($url, RITsGatewayService::$credentials->headers, json_encode($phpArray));
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

        echo "\n\n\n";
        echo 'URL: ', $url;
        echo "\n";
        echo 'HEADERS: ', json_encode(RITsGatewayService::$credentials->headers);
        echo "\n";

        $addAccount->accountNo = $accountno_encrypted;
        $addAccount->bankCode = $bankcode_encrypted;

        echo "\n";
        echo '$accountno_encrypted: ', $addAccount->accountNo;
        echo "\n";
        echo '$bankcode_encrypted: ', $addAccount->bankCode;
        echo "\n";

        // POST BODY
        $phpArray = array(
            'accountNo' => $addAccount->accountNo,
            'bankCode' => $addAccount->bankCode
        );

        // POST CALL
        $result = HTTPUtil::postMethod($url, RITsGatewayService::$credentials->headers, json_encode($phpArray));
        return json_decode($result, AddAccountResponse::class);
    }

    // PAYMENT STATUS SINGLE
    public static function paymentStatusSingle($transRef)
    {
        $url = RITsGatewayService::$credentials->url . ApplicationUrl::$paymentStatusSingle;

        echo "\n\n\n";
        echo 'URL: ', $url;

        $key = utf8_encode(RITsGatewayService::$credentials->key);
        $iv = utf8_encode(RITsGatewayService::$credentials->iv);

        $transRef_encrypted = AES128CBC::encrypt($transRef, $iv, $key);
        echo "\n\n";
        echo '$transRef_encrypted: ', $transRef_encrypted;
        echo "\n";
        echo 'HEADERS: ', json_encode(RITsGatewayService::$credentials->headers);
        echo "\n";

        // POST BODY
        $phpArray = array(
            'transRef' => $transRef_encrypted
        );

        // POST CALL
        $result = HTTPUtil::postMethod($url, RITsGatewayService::$credentials->headers, json_encode($phpArray));
        return json_decode($result, PaymentStatusSingleResponse::class);
    }

    // PAYMENT STATUS BULK
    public static function paymentStatusBulk($batchRef)
    {
        $url = RITsGatewayService::$credentials->url . ApplicationUrl::$paymentStatusBulk;

        echo "\n\n\n";
        echo 'URL: ', $url;

        $key = utf8_encode(RITsGatewayService::$credentials->key);
        $iv = utf8_encode(RITsGatewayService::$credentials->iv);

        $batchRef_encrypted = AES128CBC::encrypt($batchRef, $iv, $key);
        echo "\n\n";
        echo '$transRef_encrypted: ', $batchRef_encrypted;
        echo "\n";
        echo 'HEADERS: ', json_encode(RITsGatewayService::$credentials->headers);
        echo "\n";

        // POST BODY
        $phpArray = array(
            'batchRef' => $batchRef_encrypted
        );

        // POST CALL
        $result = HTTPUtil::postMethod($url, RITsGatewayService::$credentials->headers, json_encode($phpArray));
        return json_decode($result, PaymentStatusBulkResponse::class);
    }

    // PAYMENT SINGLE
    public static function singlePayment($paymentSingleRequest)
    {
        $url = RITsGatewayService::$credentials->url . ApplicationUrl::$paymentSingle;

        echo "\n\n\n";
        echo 'URL: ', $url;

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

        echo "\n\n";
        echo 'HEADERS: ', json_encode(RITsGatewayService::$credentials->headers);
        echo "\n";

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
        $result = HTTPUtil::postMethod($url, RITsGatewayService::$credentials->headers, json_encode($phpArray));

        return json_decode($result, PaymentSingleResponse::class);
    }

    // PAYMENT BULK
    public static function bulkPayment($paymentBulkRequest)
    {
        $url = RITsGatewayService::$credentials->url . ApplicationUrl::$paymentBulk;

        echo "\n\n";
        echo 'URL: ', $url;

        echo "\n\n";
        echo 'HEADERS: ', json_encode(RITsGatewayService::$credentials->headers);
        echo "\n";

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

        echo "\n\n";
        echo 'encryptedBulkPaymentInfo: ', json_encode($encrypted_paymentBulkRequest->bulkPaymentInfo);
        echo "\n\n";
        echo 'encryptedPaymentDetails: ', json_encode($encrypted_paymentBulkRequest->paymentDetails);
        echo "\n\n";

        // POST BODY
        $phpArray = array(
            'bulkPaymentInfo' => $encrypted_paymentBulkRequest->bulkPaymentInfo,
            'paymentDetails' => $encrypted_paymentBulkRequest->paymentDetails
        );

        // // POST CALL
        $result = HTTPUtil::postMethod($url, RITsGatewayService::$credentials->headers, json_encode($phpArray));

        return json_decode($result, PaymentBulkResponse::class);
    }

    // VALIDATE ACCOUNT OTP
    public static function validateAccountOTP($validateAccountOTPRequest)
    {
        $url = RITsGatewayService::$credentials->url . ApplicationUrl::$validateAccountOTP;

        echo "\n\n\n";
        echo 'URL: ', $url;
        echo "\n";
        echo 'HEADERS: ', json_encode(RITsGatewayService::$credentials->headers);
        echo "\n";

        echo "\n";
        echo '$remitaTransRef_encrypted: ', $validateAccountOTPRequest->remitaTransRef;
        echo "\n";

        // POST CALL
        $result = HTTPUtil::postMethod($url, RITsGatewayService::$credentials->headers, json_encode($validateAccountOTPRequest));
        return json_decode($result, ValidateAccountOTPResponse::class);
    }
}


function initTest()
{
    // RPG SDK Credentials setup
    $merchantId = "KUDI1234";
    $apiKey = "S1VESTEyMzR8S1VESQ==";
    $requestId = round(microtime(true) * 1000);
    $timeStamp = "2019-09-11T05:33:39+000000";
    $apiToken = "dWFBTVVGTGZRUEZaemRvVC8wYVNuRkVTc2REVi9GWGdCMHRvWHNXTnovaz0=";
    $key = "cymsrniuxqtgfzva";
    $iv = "czidrfwqugpaxvkj";
    $apiHash = hash('sha512', $apiKey . $requestId . $apiToken);

    $headers = array(
        'Content-Type: application/json',
        'API_KEY:' . $apiKey,
        'REQUEST_ID:' . $requestId,
        'REQUEST_TS:' . $timeStamp,
        'API_DETAILS_HASH:' . $apiHash,
        'MERCHANT_ID:' . $merchantId
    );

    // INIT CREDENTIALS
    $credentials = new Credentials();
    $credentials->url = ApplicationUrl::$demoUrl;
    $credentials->headers = $headers;
    $credentials->key = $key;
    $credentials->iv = $iv;
    return $credentials;
}

class TestRITSServices
{

    function test()
    {
        RITsGatewayService::init(initTest());
        // RITsGatewayService::init(null);

        // ACTIVE BANKS ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        // $response = RITsGatewayService::activeBanks();
        // echo json_encode($response);

        // ACCOUNT INQUIRY++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        $accountEnquiry = new AccountEnquiryRequest();
        $accountEnquiry->accountNo = "044332222";
        $accountEnquiry->bankCode = "044";
        $response = RITsGatewayService::accountInquiry($accountEnquiry);
        echo json_encode($response);

        // ADD ACCOUNT++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        $addAccountRequest = new AddAccountRequest();
        $addAccountRequest->accountNo = "044332222";
        $addAccountRequest->bankCode = "044";
        $response = RITsGatewayService::addAccount($addAccountRequest);
        echo json_encode($response);

        // VALIDATE ACCOUNT OTP++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        $otp = "1234";
        $card = "0441234567890";
        $remitaTransRef = "MTUxNjYwOTcxNzM3MQ==";

        $key = utf8_encode(RITsGatewayService::$credentials->key);
        $iv = utf8_encode(RITsGatewayService::$credentials->iv);

        // ENCRYPTING DATA
        $otp_encrypted = AES128CBC::encrypt($otp, $iv, $key);
        $card_encrypted = AES128CBC::encrypt($card, $iv, $key);
        $remitaTransRef_encrypted = AES128CBC::encrypt($remitaTransRef, $iv, $key);

        $validateAccountOTPRequest = new ValidateAccountOTPRequest();
        $validateAccountOTPRequest->remitaTransRef = $remitaTransRef_encrypted;

        $authParams = new AuthParams();
        $authParams->param1 = "OTP";
        $authParams->value = $otp_encrypted;

        $authParams2 = new AuthParams();
        $authParams2->param2 = "CARD";
        $authParams2->value = $card_encrypted;

        $validateAccountOTPRequest->setAuthParams(array(
            $authParams,
            $authParams2
        ));
        // $response = RITsGatewayService::validateAccountOTP($validateAccountOTPRequest);
        // echo json_encode($response);

        // SINGLE PAYMENT STATUS ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        $transRef = "318187";
        $response = RITsGatewayService::paymentStatusSingle($transRef);
        echo json_encode($response);

        // BULK PAYMENT STATUS ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        $batchRef = "13441234556";
        // $response = RITsGatewayService::paymentStatusBulk($batchRef);
        // echo json_encode($response);

        // PAYMENT SINGLE++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        $paymentSingleRequest = new PaymentSingleRequest();
        $paymentSingleRequest->fromBank = "044";
        $paymentSingleRequest->debitAccount = "1234565678";
        $paymentSingleRequest->toBank = "058";
        $paymentSingleRequest->creditAccount = "0582915208017";
        $paymentSingleRequest->narration = "Regular Payment";
        $paymentSingleRequest->amount = "5000";
        $paymentSingleRequest->beneficiaryEmail = "qa@test.com";
        // $response = RITsGatewayService::singlePayment($paymentSingleRequest);
        // echo json_encode($response);

        // PAYMENT BULK ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        $paymentBulkRequest = new PaymentBulkRequest();
        $paymentDetails1 = new PaymentDetails();
        $paymentDetails1->transRef = rand();
        $paymentDetails1->narration = "Regular Payment";
        $paymentDetails1->amount = "5000";
        $paymentDetails1->benficiaryEmail = "qa@test.com";
        $paymentDetails1->benficiaryBankCode = "058";
        $paymentDetails1->benficiaryAccountNumber = "0582915208017";

        $paymentDetails2 = new PaymentDetails();
        $paymentDetails2->transRef = rand();
        $paymentDetails2->narration = "Regular Payment";
        $paymentDetails2->amount = "6000";
        $paymentDetails2->benficiaryEmail = "qa@test.com";
        $paymentDetails2->benficiaryBankCode = "058";
        $paymentDetails2->benficiaryAccountNumber = "0582915208017";

        $paymentDetails3 = new PaymentDetails();
        $paymentDetails3->transRef = rand();
        $paymentDetails3->narration = "Regular Payment";
        $paymentDetails3->amount = "3000";
        $paymentDetails3->benficiaryEmail = "qa@test.com";
        $paymentDetails3->benficiaryBankCode = "058";
        $paymentDetails3->benficiaryAccountNumber = "0582915208017";

        $bulkPaymentInfo = new BulkPaymentInfo();
        $bulkPaymentInfo->totalAmount = $paymentDetails1->amount + $paymentDetails2->amount + $paymentDetails3->amount;

        $bulkPaymentInfo->batchRef = rand() * 777;
        $bulkPaymentInfo->debitAccount = "1234565678";
        $bulkPaymentInfo->narration = "Regular Payment";
        $bulkPaymentInfo->bankCode = "044";

        $paymentBulkRequest->bulkPaymentInfo = $bulkPaymentInfo;
        $paymentBulkRequest->setPaymentDetails(array(
            $paymentDetails1,
            $paymentDetails2,
            $paymentDetails3
        ));

        // $response = RITsGatewayService::bulkPayment($paymentBulkRequest);
        // echo json_encode($response);
    }
}

$testRITs = new TestRITSServices();
$testRITs->test();


?>


<?php

include 'RITsGatewayService.php';

function initTest()
{
    // RPG SDK Credentials setup
    $merchantId = "KUDI1234";
    $apiKey = "S1VESTEyMzR8S1VESQ==";
    $key = "cymsrniuxqtgfzva";
    $iv = "czidrfwqugpaxvkj";
    $apiToken = "dWFBTVVGTGZRUEZaemRvVC8wYVNuRkVTc2REVi9GWGdCMHRvWHNXTnovaz0=";

    // INIT CREDENTIALS
    $credentials = new Credentials();
    $credentials->url = ApplicationUrl::$demoUrl;
    $credentials->key = $key;
    $credentials->iv = $iv;
    $credentials->apiToken = $apiToken;
    $credentials->apiKey = $apiKey;
    $credentials->merchantId = $merchantId;

    return $credentials;
}

class TestRITSServices
{

    function test()
    {
        RITsGatewayService::init(initTest());

        // ACTIVE BANKS ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        $response = RITsGatewayService::activeBanks();
        echo "\n\n";
        echo "ACTIVE BANKS: ", json_encode($response);

        // ACCOUNT INQUIRY++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        $accountEnquiry = new AccountEnquiryRequest();
        $accountEnquiry->accountNo = "044332222";
        $accountEnquiry->bankCode = "044";
        $response = RITsGatewayService::accountInquiry($accountEnquiry);
        echo "\n\n";
        echo "ACCOUNT INQUIRY: ", json_encode($response);

        // ADD ACCOUNT++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        $addAccountRequest = new AddAccountRequest();
        $addAccountRequest->accountNo = "044332222";
        $addAccountRequest->bankCode = "044";
        $response = RITsGatewayService::addAccount($addAccountRequest);
        echo "\n\n";
        echo "ADD ACCOUNT: ", json_encode($response);

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
        $response = RITsGatewayService::validateAccountOTP($validateAccountOTPRequest);
        echo "\n\n";
        echo "VALIDATE ACCOUNT OTP: ", json_encode($response);

        // SINGLE PAYMENT STATUS ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        $transRef = "318187";
        $response = RITsGatewayService::paymentStatusSingle($transRef);
        echo "\n\n";
        echo "SINGLE PAYMENT STATUS : ", json_encode($response);

        // BULK PAYMENT STATUS ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        $batchRef = "13441234556";
        $response = RITsGatewayService::paymentStatusBulk($batchRef);
        echo "\n\n";
        echo "BULK PAYMENT STATUS: ", json_encode($response);

        // PAYMENT SINGLE++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        $paymentSingleRequest = new PaymentSingleRequest();
        $paymentSingleRequest->fromBank = "044";
        $paymentSingleRequest->debitAccount = "1234565678";
        $paymentSingleRequest->toBank = "058";
        $paymentSingleRequest->creditAccount = "0582915208017";
        $paymentSingleRequest->narration = "Regular Payment";
        $paymentSingleRequest->amount = "5000";
        $paymentSingleRequest->beneficiaryEmail = "qa@test.com";
        $response = RITsGatewayService::singlePayment($paymentSingleRequest);
        echo "\n\n";
        echo "PAYMENT SINGLE: ",json_encode($response);

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

        $response = RITsGatewayService::bulkPayment($paymentBulkRequest);
        echo "\n\n";
        echo "PAYMENT BULK: ",json_encode($response);
    }
}

$testRITs = new TestRITSServices();
$testRITs->test();

?>


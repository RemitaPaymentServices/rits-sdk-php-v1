<?php
include 'RITsGatewayService.php';

function initTest()
{
    // RPG SDK Credentials setup
    $merchantId = "DEMOMDA1234";
    $apiKey = "REVNT01EQTEyMzR8REVNT01EQQ==";
    $key = "nbzjfdiehurgsxct";
    $iv = "sngtmqpfurxdbkwj";
    $apiToken = "bmR1ZFFFWEx5R2c2NmhnMEk5a25WenJaZWZwbHFFYldKOGY0bHlGZnBZQ1N5WEpXU2Y1dGt3PT0=";

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
        echo "\n";
        echo "// ACTIVE BANKS ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++";
        $response = RITsGatewayService::activeBanks();
        echo "\n";
        echo "STATUS: ", $response->status;
        echo "\n";
        echo "CODE: ", $response->data->responseCode;
        echo "\n";
        echo "ARRAY: ", json_encode($response->data->banks[0]);
        echo "\n";
        echo "DATA: ", json_encode($response->data);

        echo "\n\n\n";
        echo "// ACCOUNT INQUIRY++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++";
        $accountEnquiry = new AccountEnquiryRequest();
        $accountEnquiry->accountNo = "4589999044";
        $accountEnquiry->bankCode = "044";
        $response = RITsGatewayService::accountInquiry($accountEnquiry);
        echo "\n";
        echo "STATUS: ", $response->status;
        echo "\n";
        echo "CODE: ", $response->data->responseCode;
        echo "\n";
        echo "DATA: ", json_encode($response->data);

        echo "\n\n\n";
        echo "// SINGLE PAYMENT ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++";
        $paymentSingleRequest = new PaymentSingleRequest();
        $paymentSingleRequest->fromBank = "058";
        $paymentSingleRequest->debitAccount = "8909090989";
        $paymentSingleRequest->toBank = "044";
        $paymentSingleRequest->creditAccount = "4589999044";
        $paymentSingleRequest->narration = "Regular Payment";
        $paymentSingleRequest->amount = "500";
        $paymentSingleRequest->beneficiaryEmail = "qa@test.com";
        $response = RITsGatewayService::singlePayment($paymentSingleRequest);
        echo "\n";
        echo "STATUS: ", $response->status;
        echo "\n";
        echo "CODE: ", $response->data->responseCode;
        echo "\n";
        echo "DATA: ", json_encode($response->data);

        echo "\n\n\n";
        echo "// SINGLE PAYMENT STATUS ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++";
        $transRef = "986134";
        $response = RITsGatewayService::paymentStatusSingle($transRef);
        echo "\n";
        echo "STATUS: ", $response->status;
        echo "\n";
        echo "CODE: ", $response->data->responseCode;
        echo "\n";
        echo "DATA: ", json_encode($response->data);

        echo "\n\n\n";
        echo "// BULK PAYMENT ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++";
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
        $bulkPaymentInfo->debitAccount = "8909090989";
        $bulkPaymentInfo->narration = "Regular Payment";
        $bulkPaymentInfo->bankCode = "058";

        $paymentBulkRequest->bulkPaymentInfo = $bulkPaymentInfo;
        $paymentBulkRequest->setPaymentDetails(array(
            $paymentDetails1,
            $paymentDetails2,
            $paymentDetails3
        ));

        $response = RITsGatewayService::bulkPayment($paymentBulkRequest);
        echo "\n";
        echo "STATUS: ", $response->status;
        echo "\n";
        echo "CODE: ", $response->data->responseCode;
        echo "\n";
        echo "DATA: ", json_encode($response);

        echo "\n\n\n";
        echo "// BULK PAYMENT STATUS ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++";
        $batchRef = "13441234556";
        $response = RITsGatewayService::paymentStatusBulk($batchRef);
        echo "\n";
        echo "STATUS: ", $response->status;
        echo "\n";
        echo "CODE: ", $response->data->bulkPaymentStatusInfo->statusCode;
        echo "\n";
        echo "DATA: ", json_encode($response);
    }
}

$testRITs = new TestRITSServices();
$testRITs->test();

?>


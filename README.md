# Remita Interbank Transfer Service (RITs) PHP SDK
This is the PHP SDK for the Remita Interbank Transfer Service.

# Prerequisites
The workflow to getting started on RITs is as follows:
Register a profile on Remita: You can visit Remita to sign-up if you are not already registered as a merchant/biller on the platform.
Receive the Remita credentials that certify you as a Biller: SystemSpecs will send you your merchant ID and an API Key necessary to secure your handshake to the Remita platform.

## Requirements
SDK works with PHP 5.7 or above.

# Basic Usage
## Configuration
All merchant credentials needed to use RITs are being setup by instantiating the Credential Class and set the properties in this class accordingly. Properties such as MerchantId, ApiKey, ApiToken, Key, Iv and the Environment needs to be set.

Note: Environment can either be $demoUrl or $liveUrl, each of this environment has it respective Credential. Ensure you set the right credentials. By default Environment is $demoUrl.
 ```php
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
    
    RITsGatewayService::init($credentials);
 ```
## CREDENTIALS
Before calling the RITs methods, the SDK needs to be initialized with the Credentials object, see below:
### Credentials attributes
|Field  | Type    | Required   | Description   |   
| ---   | ------  | -----------| -------- |   
| $merchantId| String | Yes| SystemSpecs will send you your merchant ID necessary to secure your handshake to the Remita platform.
| $apiKey | String | Yes| SystemSpecs will send you your an API Key necessary to secure your handshake to the Remita platform.
| url | String | Yes| ApplicationUrl::$demoUrl for Demo server. While ApplicationUrl::$liveUrl for Production server.
| $iv | String | Yes| SystemSpecs will provide an iv required to encrypt your request data with AES to secure your handshake to the Remita platform.
| $key | String | Yes| SystemSpecs will provide a key required to encrypt your request data with AES to secure your handshake to the Remita platform.
| $apiToken | String | Yes| SystemSpecs will send you your an API Token necessary to secure your handshake to the Remita platform.

# METHODS
## Adding Account(s)
Adding an account to your merchant profile on the RITs is a dual process.
The first step is to AddAccount, Fields required to add account includes the following;
accountNo: This is the number of the bank account being linked to merchant profile
bankCode: This is the CBN code of the bank in which the account is domiciled
transRef: This uniquely identifies the transaction
requestId: This uniquely identifies the request
```php
$addAccountRequest = new AddAccountRequest();
$addAccountRequest->accountNo = "044332222";
$addAccountRequest->bankCode = "044";
$response = RITsGatewayService::addAccount($addAccountRequest);
 ```
### $response attributes
| Name  | Type    | 
| ---   | ------  | 
| $status | String |
| $data | Data |  

### $data attributes
| Name  | Type    |
| ---   | ------  | 
| $remitaTransRef | String |
| $authParams | array(AuthParms) |  
| $responseId | String |
| $responseCode | String |
| $responseDescription | String |
| $mandateNumber | array(Objects) |
 
### $authParams attributes
| Name  | Type    |
| ---   | ------  | 
| $description1 | String |
| $description2 | String |  
| $label1 | String |
| $label2 | String |
| $param1 | String |
| $param2 | String |
Note: See 'TestRITSServices.php' in SDK on how to reference response data/attributes.

## Validate Accounts
The second step validates the account holder via bank authentication on the account details. You will be required by your bank to validate the account details the AddAccount request is being issued for, required fields(Payloads) are as follow;
card: This is the one of the authentication detail required by the bank from the account owner to validate AddAccount request
otp: This is the another authentication detail required by the bank from the account owner to validate AddAccount request
remitaTransref: This uniquely identifies the specific add account request the validation is being called for
requestId: This uniquely identifies the request

``` php
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
```
### $response attributes
| Name  | Type    | 
| ---   | ------  | 
| $status | String |
| $data | Data |  

### $data attributes
| Name  | Type    |
| ---   | ------  | 
| $remitaTransRef | String |
| $accountToken | array(undefined) |  
| $responseId | String |
| $responseCode | String |
| $responseDescription | String |
Note: See 'TestRITSServices.php' in SDK on how to reference response data/attributes.

## Payments
Payments on the RITs platform can only be made from Remita-identifiable accounts. This means that before an account can be debited on the RITs, it must be linked to a profile. Merchants may process payments via the following SDK methods on the platform:

##Single Payment Request: 
This charges/debits a merchant’s account with a specified amount to credit a designated beneficiary account. Fields(payload) to set include:
fromBank: This is the CBN code of the funding bank
debitAccount: This is the funding account number
toBank: The CBN code of destination bank where account number to be credited is domiciled. (You can use the Banks Enquiry method to get the list of all supported Banks’ code).
creditAccount: This is the account number to be credited in destination bank.
narration: The narration of the payment. This will typically be visible both in the debit and credit account statement. Max length 30 characters
amount: The amount to be debited from the debitAccountToken and credited to creditAccount in bank toBank. Format - ##.##
beneficiaryEmail: Email of the beneficiary (email of creditAccount holder)
transRef: A unique reference that identifies a payment request. This reference can be used sub- sequently to retrieve the details/status of the payment request
requestId: This uniquely identifies the request

```php
        $paymentSingleRequest = new PaymentSingleRequest();
        $paymentSingleRequest->fromBank = "044";
        $paymentSingleRequest->debitAccount = "1234565678";
        $paymentSingleRequest->toBank = "058";
        $paymentSingleRequest->creditAccount = "0582915208017";
        $paymentSingleRequest->narration = "Regular Payment";
        $paymentSingleRequest->amount = "5000";
        $paymentSingleRequest->beneficiaryEmail = "qa@test.com";
       $response = RITsGatewayService::singlePayment($paymentSingleRequest);
```
### $response attributes
| Name  | Type    | 
| ---   | ------  | 
| $status | String |
| $data | Data |  

### $data attributes
| Name  | Type    |
| ---   | ------  | 
| $authorizationId | String |
| $transRef | String |  
| $transDate | Date |
| $rrr | String |
| $paymentDate | Date |
| $responseId | String |
| $responseCode | String |
| $responseDescription | String |
| $data | array(undefined) |
Note: See 'TestRITSServices.php' in SDK on how to reference response data/attributes.

##Bulk Send Payment Request: 
Here, a single amount is debited to credit multiple accounts across several banks. Fields(payload) to set include the bulkPaymentInfo Parameters and paymentDetails Parameters

bulkPaymentInfo Payload
batchRef: A unique reference that identifies a bulk payment request.
debitAccount: Funding account number
bankCode: 3 digit code representing funding bank
creditAccount: This is the account number to be credited in destination bank.
narration: Description of the payment
requestId: This uniquely identifies the request
paymentDetails Payload

beneficiaryBankCode: The CBN code of destination bank where account number to be credited is domiciled. (You can use the Banks Enquiry method to get the list of all supported Banks’ code)
beneficiaryAccountNumber: This is the account number to be credited in destination bank.
narration: The narration of the payment. This will typically be visible both in the debit and credit account statement. Max length 30 characters
amount: The amount to be debited from the debitAccountToken and credited to creditAccount in bank toBank
beneficiaryEmail: Email of the beneficiary
transRef: A unique reference that identifies a payment request. This reference can be used sub- sequently to retrieve the details/status of the payment request.

```php
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
        $bulkPaymentInfo->batchRef = "1344126773455379773111";
        $bulkPaymentInfo->debitAccount = "1234565678";
        $bulkPaymentInfo->narration = "Regular Payment";
        $bulkPaymentInfo->bankCode = "044";

        $paymentBulkRequest->bulkPaymentInfo = $bulkPaymentInfo;
        $paymentBulkRequest->setPaymentDetails(array(
            $paymentDetails1,
            $paymentDetails2,
            $paymentDetails3
        ));

     $response =  RITsGatewayService::bulkPayment($paymentBulkRequest);

```

## Payment Request Status
The payment request status method essentially retrieves the status of a previous payment request(Single payment and Bulk payment) using its transaction reference.

##Single Payment Request Status:
transRef: This should be the same transRef that was used for the single payment request

```php
$transRef = "318187";
$response = RITsGatewayService::paymentStatusSingle($transRef);
```
### $response attributes
| Name  | Type    | 
| ---   | ------  | 
| $status | String |
| $data | Data |  

### $data attributes
| Name  | Type    |
| ---   | ------  | 
| $authorizationId | String |
| $transRef | String |  
| $debitAccount | String |
| $toBank | String |
| $creditAccount | String |
| $narration | String |
| $amount | String |
| $feeAmount | String |
| $paymentStatus | String |
| $settlementDate | Date |
| $paymentDate | Date |
| $currencyCode | array(undefined) |
| $paymentStatusCode | String |
| $responseCode | String |
| $responseDescription | String |
| $paymentDate | Date |
##Bulk Send Payment Request Status:
batchRef: This should be the same batchRef that was used for the bulk payment request
```php
$batchRef = "13441234556";
$response = RITsGatewayService::paymentStatusBulk($batchRef);
```
### $response attributes
| Name  | Type    | 
| ---   | ------  | 
| $status | String |
| $data | Data |  

### $data attributes
| Name  | Type    |
| ---   | ------  | 
| $bulkRef | String |
| $batchRef | String |  
| $bulkPaymentStatusInfo | BulkPaymentStatusInfo |
| $paymentDetails | array(PaymentDetails) |

### $bulkPaymentStatusInfo attributes
| Name  | Type    |
| ---   | ------  | 
| $debitAccountToken | String |
| $statusCode | String |  
| $statusMessage | String |
| $totalAmount | double |
| $feeAmount | double |
| $currencyCode | String |
| $responseCode | String |
| $responseMessage | String |
| $paymentState | String |

### $paymentDetails attributes
| Name  | Type    |
| ---   | ------  | 
| $transRef | String |
| $paymentReference | String |  
| $authorizationId | String |
| $transDate | Date |
| $paymentDate | Date |
| $statusCode | String |
| $statusMessage | String |
| $amount | int |
| $paymentState | String |
| $responseCode | String |
| $responseMessage | String |
Note: See 'TestRITSServices.php' in SDK on how to reference response data/attributes.

## Account Enquiry
Payment Request Status finds all available information on a specific account, required fields(Payloads) are as follow;
   1. accountNo: Account number of tokenized account to be looked up.
   2. bankCode: The bank code where the account is domiciled. Use the Banks Enquiry method.
```php
$accountEnquiry = new AccountEnquiryRequest();
$accountEnquiry->accountNo = "044332222";
$accountEnquiry->bankCode = "044";
$response = RITsGatewayService::accountInquiry($accountEnquiry);
```
### $response attributes
| Name  | Type    | 
| ---   | ------  | 
| $status | String |
| $data | Data |  

### $data attributes
| Name  | Type    |
| ---   | ------  | 
| $accountName | String |  
| $accountNo | Date |
| $bankCode | String |
| $phoneNumber | Date |
| $responseId | String |
| $responseCode | String |
| $responseDescription | String |
| $email | array(undefined) |
Note: See 'TestRITSServices.php' in SDK on how to reference response data/attributes.

## Bank Enquiry
This method lists the banks that are active on the RITs platform. 
```php
$response = RITsGatewayService::activeBanks();
````
### $response attributes
| Name  | Type    | 
| ---   | ------  | 
| $status | String |
| $data | Data |  

### $data attributes
| Name  | Type    |
| ---   | ------  | 
| $responseId | String |
| $responseCode | String |
| $responseDescription | String |
| $banks | array(Banks) |

### $banks attributes
| Name  | Type    |
| ---   | ------  | 
| $bankCode | String |
| $bankName | String |
| $bankAccronym | String |
| $type | String |
Note: See 'TestRITSServices.php' in SDK on how to reference response data/attributes.


## Support
For all other support needs, support@remita.net
 

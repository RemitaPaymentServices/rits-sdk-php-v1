<?php

class PaymentBulkRequest
{

    public $bulkPaymentInfo;

    // BulkPaymentInfo
    public $paymentDetails = Array(
        PaymentDetails::class
    );

    // array( PaymentDetails )
    public function getPaymentDetails()
    {
        return $this->paymentDetails;
    }

    public function setPaymentDetails($paymentDetails)
    {
        $this->paymentDetails = $paymentDetails;
    }
}

?>

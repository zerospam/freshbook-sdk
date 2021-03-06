<?php
/**
 * Created by PhpStorm.
 * User: ycoutu
 * Date: 17/07/18
 * Time: 11:45 AM
 */

namespace ZEROSPAM\Freshbooks\Business\Report\PaymentsCollected;

use Carbon\Carbon;
use ZEROSPAM\Framework\SDK\Response\Api\BaseResponse;
use ZEROSPAM\Freshbooks\Business\Amount;
use ZEROSPAM\Freshbooks\Business\Enums\Payment\PaymentMethodEnum;

/**
 * Class Payment
 *
 * Payments collected report payment
 *
 * Invoice related information is null case of an overpayment ;
 * Overpayment payment will be registered as 2 payments by freshbook
 * 1. First one for the invoice paid part with the related invoice bindings
 * 2. Second one is overpaid chunk and invoice related values will be null
 *
 * @property-read int|null          $invoiceid
 * @property-read string            $description
 * @property-read int               $clientid
 * @property-read Amount            $amount
 * @property-read string            $client
 * @property-read Carbon            $date
 * @property-read string|null       $invoice_number
 * @property-read PaymentMethodEnum $method
 *
 * @package ZEROSPAM\Freshbooks\Business\Report\PaymentsCollected
 */
class Payment extends BaseResponse
{
    protected $dates = [
        'date',
    ];

    /**
     * Amount
     *
     * @return Amount
     */
    public function getAmountAttribute(): Amount
    {
        return new Amount($this->data()['amount']);
    }

    /**
     * @return PaymentMethodEnum
     */
    public function getMethodAttribute(): PaymentMethodEnum
    {
        return PaymentMethodEnum::byValueInsensitive($this->data()['method']);
    }
}

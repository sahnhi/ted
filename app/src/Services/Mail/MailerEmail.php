<?php

namespace App\Services\Mail;

use App\Services\Mail\OutOfStock\OutOfStockCustomerEmail;
use App\Services\Mail\ReturnMail\AutonomousReturnEmail;
use App\Services\Mail\ReturnMail\LabelEmail;
use App\Services\Mail\ReturnMail\ProductReturnEmail;

class MailerEmail
{
    public function __construct(
        protected readonly OrderConfirmationEmail $orderConfirmationEmail,
        protected readonly QuotationEmail $quotationEmail,
        protected readonly ReturningProductsEmail $returningProducts,
        protected readonly ReviewEmail $reviewEmail,
        protected readonly TrackingEmail $trackingEmail,
        protected readonly LabelEmail $labelEmail,
        protected readonly ProductReturnEmail $productReturnEmail,
        protected readonly RefundConfirmationEmail $refundConfirmationEmail,
        protected readonly ReturnReceptionEmail $returnReceptionEmail,
        protected readonly OutOfStockCustomerEmail $outOfStockCustomerEmail,
        protected readonly AutonomousReturnEmail $autonomousReturnEmail,
        private readonly EmailSenderInterface $emailSender,
        protected readonly PayByLinkEmail $payByLinkEmail
    ) {
    }

    /**
     * @throws \Exception
     */
    public function sendEmail(string $type, $order, ?array $data = []): void
    {
        switch ($type) {
            case MailerEmailInterface::TYPE_ORDER_CONFIRMATION_EMAIL:
                $templateVars = $this->orderConfirmationEmail->generateEmail($order);
                $this->emailSender->send($order, $templateVars, MandrillEmail::MERGE_LANGUAGE_HANDLEBARS);
                break;
            case MailerEmailInterface::TYPE_QUOTATION_EMAIL:
                $this->quotationEmail->generateEmail($order);
                break;
            case MailerEmailInterface::TYPE_RETURNING_PRODUCTS_EMAIL:
                $this->returningProducts->generateEmail($order, $data);
                break;
            case MailerEmailInterface::TYPE_REVIEW_EMAIL:
                $this->reviewEmail->generateEmail($order);
                break;
            case MailerEmailInterface::TYPE_TRACKING_EMAIL:
                $this->trackingEmail->generateEmail($order);
                break;
            case MailerEmailInterface::TYPE_LABEL_EMAIL:
                $this->labelEmail->generateEmail($order, $data['returnOrder'], $data['templateOptions'], $data['files']);
                break;
            case MailerEmailInterface::TYPE_PRODUCT_RETURN_EMAIL:
                $this->productReturnEmail->generateEmail($order, $data['returnOrder'], $data['returnFormType'], $data['templateOptions']);
                break;
            case MailerEmailInterface::TYPE_REFUND_CONFIRMATION_EMAIL:
                $this->refundConfirmationEmail->generateEmail($order, $data['payment']);
                break;
            case MailerEmailInterface::TYPE_RETURN_RECEPTION_EMAIL:
                $this->returnReceptionEmail->generateEmail($order, $data['templateOptions']);
                break;
            case MailerEmailInterface::TYPE_OUT_OF_STOCK_CUSTOMER_EMAIL:
                $this->outOfStockCustomerEmail->generateEmail($order, $data['date']);
                break;
            case AutonomousReturnEmail::class:
                $this->autonomousReturnEmail->generateEmail($order);
                break;
            case PayByLinkEmail::class:
                $this->payByLinkEmail->generateEmail($order, $data['link']);
                break;
            default:
                break;
        }
    }
}

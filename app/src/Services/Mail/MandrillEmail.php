<?php

namespace App\Services\Mail;

use App\Doctrine\Entity\Sylius\Order;
use MailchimpTransactional\ApiClient;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class MandrillEmail implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public const MERGE_LANGUAGE_MAILCHIMP = 'mailchimp';
    public const MERGE_LANGUAGE_HANDLEBARS = 'handlebars';

    private ApiClient $mailchimpClient;

    public function __construct(string $mandrillApiKey)
    {
        $this->mailchimpClient = new ApiClient();
        $this->mailchimpClient->setApiKey($mandrillApiKey);
        $this->logger = new NullLogger();
    }

    public function generateEmail(
        $order,
        string $templateName,
        array $templateVars,
        string $mergeLanguage,
        ?array $cc = null,
        ?array $files = null
    ): void {
        // get data for mail
        $customer = $order->getCustomer();

        $to[] = [
            'email' => $customer->getEmail(),
            'name' => $customer->getFullName(),
            'type' => 'to',
        ];

        if (null !== $cc) {
            foreach ($cc as $name => $email) {
                $to[] = [
                    'email' => $email,
                    'name' => $name,
                    'to' => 'cc',
                ];
            }
        }
        // prepare args for mandrill call
        $message = [
            // 'subject' => 'Merci de votre commande', //done from mandrill
            // 'from_email' => 'hello@tediber.com', //done from mandrill
            'from_name' => 'Tediber',
            'to' => $to,
            'headers' => ['Reply-To' => 'hello@tediber.com'],
            'important' => false,
            'track_opens' => true,
            'track_clicks' => true,
            'auto_text' => null,
            'auto_html' => null,
            'inline_css' => null,
            'url_strip_qs' => null,
            'preserve_recipients' => null,
            'view_content_link' => null,
            'bcc_address' => null,
            'tracking_domain' => null,
            'signing_domain' => null,
            'return_path_domain' => null,
            'merge' => true,
            'merge_language' => $mergeLanguage, // Value: mailchimp or handlebars
            'global_merge_vars' => $templateVars,
            'merge_vars' => [],
            'tags' => ['payment-confirm'],
            'subaccount' => null,
            'google_analytics_domains' => [],
            'google_analytics_campaign' => null,
            'metadata' => [],
            'recipient_metadata' => [
                [
                    'rcpt' => $customer->getEmail(),
                    'values' => ['user_id' => $customer->getId()],
                ],
            ],
            'attachments' => $files,
        ];

        try {
            $this->logger->debug(
                'Mandrill request: {request}',
                [
                    'request' => $message,
                ]
            );

            $response = $this->mailchimpClient->messages->sendTemplate(
                [
                    'template_name' => $templateName,
                    'template_content' => [],
                    'message' => $message,
                ]
            );
            $this->logger->info(
                'Mandrill call successfully for order {orderId}. Got response {response}',
                [
                    'response' => $response,
                    'orderId' => $order->getId(),
                ]
            );
        } catch (\Exception $e) {
            $this->logger->error(
                'Mandrill call failed. Got error: {errorClass} {errorMessage}, for order {orderId}',
                [
                    'errorClass' => $e::class,
                    'errorMessage' => $e->getMessage(),
                    'orderId' => $order->getId(),
                ]
            );
        }
    }

    public function generateToAnOtherEmail(
        Order $order,
        array $customer,
        string $templateName,
        array $templateVars,
        string $mergeLanguage,
        ?array $cc = null,
        ?array $files = null
    ): void {
        $to[] = [
            'email' => $customer['email'],
            'name' => $customer['name'],
            'type' => 'to',
        ];

        if (null !== $cc) {
            foreach ($cc as $name => $email) {
                $to[] = [
                    'email' => $email,
                    'name' => $name,
                    'to' => 'cc',
                ];
            }
        }
        // prepare args for mandrill call
        $message = [
            // 'subject' => 'Merci de votre commande', //done from mandrill
            // 'from_email' => 'hello@tediber.com', //done from mandrill
            'from_name' => 'Tediber',
            'to' => $to,
            'headers' => ['Reply-To' => 'hello@tediber.com'],
            'important' => false,
            'track_opens' => true,
            'track_clicks' => true,
            'auto_text' => null,
            'auto_html' => null,
            'inline_css' => null,
            'url_strip_qs' => null,
            'preserve_recipients' => null,
            'view_content_link' => null,
            'bcc_address' => null,
            'tracking_domain' => null,
            'signing_domain' => null,
            'return_path_domain' => null,
            'merge' => true,
            'merge_language' => $mergeLanguage, // Value: mailchimp or handlebars
            'global_merge_vars' => $templateVars,
            'merge_vars' => [],
            'tags' => ['payment-confirm'],
            'subaccount' => null,
            'google_analytics_domains' => [],
            'google_analytics_campaign' => null,
            'metadata' => [],
            'recipient_metadata' => [
                [
                    'rcpt' => $customer['email'],
                    'values' => ['user_id' => $customer['id']],
                ],
            ],
            'attachments' => $files,
        ];

        try {
            $this->logger->debug(
                'Mandrill request: {request}',
                [
                    'request' => $message,
                ]
            );
            $response = $this->mailchimpClient->messages->sendTemplate(
                [
                    'template_name' => $templateName,
                    'template_content' => [],
                    'message' => $message,
                ]
            );
            $this->logger->info(
                'Mandrill call successfully for order {orderId}. Got response {response}',
                [
                    'response' => $response,
                    'orderId' => $order->getId(),
                ]
            );
        } catch (\Exception $e) {
            $this->logger->error(
                'Mandrill call failed. Got error: {errorClass} {errorMessage}, for order {orderId}',
                [
                    'errorClass' => $e::class,
                    'errorMessage' => $e->getMessage(),
                    'orderId' => $order->getId(),
                ]
            );
        }
    }
}

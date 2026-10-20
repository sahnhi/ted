<?php

declare(strict_types=1);

namespace Sokeo\SyliusYounitedpayPlugin\Payum\Action;

use App\Services\Sentry\ReportingInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\RenderTemplate;
use Sentry;
use SM\Factory\FactoryInterface;
use Sokeo\SyliusYounitedpayPlugin\Api\YounitedpayApiInterface;
use Sokeo\SyliusYounitedpayPlugin\Api\YounitedpayContract;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Webmozart\Assert\Assert;

final class CaptureAction implements ActionInterface, GatewayAwareInterface, ApiAwareInterface
{
    use GatewayAwareTrait;

    private RequestStack $requestStack;
    private FlashBagInterface $flashBag;
    private UrlGeneratorInterface $router;
    private string $captureTemplateName;
    private string $errorTemplateName;

    public function __construct(
        RequestStack $requestStack,
        FlashBagInterface $flashBag,
        UrlGeneratorInterface $router,
        string $captureTemplateName,
        string $errorTemplateName,
        private readonly ReportingInterface $reporting,
        private readonly Logger $logger,
        private readonly FactoryInterface $smFactory
    ) {
        $this->requestStack = $requestStack;
        $this->flashBag = $flashBag;
        $this->router = $router;
        $this->captureTemplateName = $captureTemplateName;
        $this->errorTemplateName = $errorTemplateName;
    }

    /** @var YounitedpayApiInterface */
    private $api;

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getModel();
        Assert::isInstanceOf($payment, PaymentInterface::class);

        if (
            !isset($payment->getDetails()['younitedpay'])
            or !isset($payment->getDetails()['younitedpay']['maturity'])
            or !in_array($payment->getDetails()['younitedpay']['maturity'], $this->api->getMaturities())
        ) {
            $this->flashBag->add('error', 'Maturité non renseignée. Impossible de continuer.');
            throw new HttpRedirect($this->requestStack->getCurrentRequest()->server->get('HTTP_REFERER'));
        }

        /** @var OrderInterface $order */
        $order = $payment->getOrder();
        Assert::isInstanceOf($order, OrderInterface::class);

        $maturity = $payment->getDetails()['younitedpay']['maturity'];
        $younitedpayContract = new YounitedpayContract($this->api, $this->logger);

        $this->gateway->execute($httpRequest = new GetHttpRequest());

        $response = $younitedpayContract->initializeContract(
            $order,
            $payment,
            $maturity,
            $this->router,
            $httpRequest
        );

        $details = $payment->getDetails();
        $younitedpayDetails = $details['younitedpay'];
        $younitedpayDetails['status_history'][date('Y-m-d_H:i:s')][$younitedpayContract->getInitializeLabel()] = [
            'statusCode' => $response['statusCode'],
        ];

        if (200 === $response['statusCode'] and !empty($response['redirectUrl'])) {
            $younitedpayDetails['contractReference'] = $response['contractReference'];
            $younitedpayDetails['current_status'] = $younitedpayContract->getInitializeLabel();
            $payment->setDetails(
                array_merge(
                    $details,
                    ['younitedpay' => $younitedpayDetails]
                )
            );

            // if source request is not api
            if (false === str_contains($request->getToken()->getAfterUrl(), 'shop-api')) {
                throw new HttpRedirect($response['redirectUrl']);
            }

            $template = new RenderTemplate($this->captureTemplateName, [
                'redirectUrl' => $response['redirectUrl'],
            ]);
            $this->gateway->execute($template);

            throw new HttpResponse($template->getResult());
        } else {
            $this->gateway->execute($errorTemplate = new RenderTemplate($this->errorTemplateName, [
                'response' => $response,
            ]));

            $this->reporting->reportIssue(
                'Younited Pay error',
                Sentry\Severity::warning(),
                [
                    'payment_method' => 'YounitedPay',
                    'order_number' => $order->getNumber(),
                ],
                ['payload' => $response]
            );

            if (!empty($response['errors'])) {
                $younitedpayDetails[$younitedpayContract->getInitializeLabel()]['errors'] = $response['errors'];
            }
            $younitedpayDetails['current_status'] = 'failed';
            $payment->setDetails(
                array_merge(
                    $details,
                    ['younitedpay' => $younitedpayDetails]
                )
            );

            throw new HttpResponse($errorTemplate->getResult(), $response['statusCode']);
        }
    }

    public function supports($request): bool
    {
        return
            $request instanceof Capture
            && $request->getModel() instanceof SyliusPaymentInterface;
    }

    public function setApi($api): void
    {
        if (!$api instanceof YounitedpayApiInterface) {
            throw new UnsupportedApiException('Not supported.');
        }

        $this->api = $api;
    }
}

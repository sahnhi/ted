<?php

namespace App\Controller\Quotation;

use App\Business\Command\Quotation;
use App\Doctrine\Entity\Sylius\Order;
use App\Form\Type\QuotationType;
use App\Handler\Front\QuotationHandler;
use Sylius\Component\Order\Context\CartContextInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class QuotationController extends AbstractController
{
    public function __construct(
        private readonly QuotationHandler $handler,
        private readonly FlashBagInterface $flashBag,
        private readonly TranslatorInterface $translator,
        private readonly CartContextInterface $cartContext,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function generateQuotationAction(Request $request): RedirectResponse
    {
        /** @var Order $cart */
        $cart = $this->cartContext->getCart();

        // An order cannot have two quotations
        if (null !== $cart->getOrderStore()) {
            $this->flashBag->add('error', $this->translator->trans('app.ui.quotation.cart_already_has_quotation'));

            return $this->redirectToRoute('sylius_shop_cart_summary');
        }

        $form = $this->createForm(QuotationType::class, new Quotation($cart));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                /** @var FormError $error */
                foreach ($form->getErrors(true) as $error) {
                    $this->flashBag->add('error', $error->getMessage());
                }

                return $this->redirectToRoute('sylius_shop_cart_summary');
            }

            /** @var Quotation $quotation */
            $quotation = $form->getData();

            $this->handler->handle($quotation);

            // We need to keep the cart in database, we therefore empty the session where the reference to the cart is
            // stored, so that we have a new and empty cart
            $request->getSession()->set('_sylius.cart.default', null);

            $this->flashBag->add('success', $this->translator->trans('app.ui.quotation.creation_success'));
        }

        return $this->redirectToRoute('sylius_shop_homepage');
    }
}

<?php

namespace App\Services;

use App\Context\CliBasedChannelContext;
use App\Doctrine\Entity\Fastmag;
use App\Doctrine\Entity\OrderStore;
use App\Doctrine\Entity\Sylius\Customer;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Repository\FamiliarityRepositoryInterface;
use App\Doctrine\Repository\SellerRepository;
use App\Doctrine\Repository\StoreRepository;
use App\Doctrine\Repository\Sylius\CustomerRepository;
use App\Doctrine\Repository\Sylius\OrderRepositoryInterface;
use App\Doctrine\Repository\Sylius\ProductVariantRepositoryInterface;
use App\Enum\EmailConfigurationName;
use App\Exception\EmailConfigurationNotFoundException;
use App\Registry\EmailConfigurationRegistryInterface;
use App\Services\Channel\ChannelCode;
use App\Services\Sentry\ReportingInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use Sentry;
use SM\Factory\Factory;
use Sylius\Bundle\ChannelBundle\Doctrine\ORM\ChannelRepository;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\OrderCheckoutTransitions;
use Sylius\Component\Core\OrderPaymentTransitions;
use Sylius\Component\Core\OrderShippingStates;
use Sylius\Component\Core\OrderShippingTransitions;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;
use Sylius\Component\Core\Repository\ShippingMethodRepositoryInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Sylius\Component\Payment\PaymentTransitions;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class FastmagImport
{
    private const COLISWEB = 'COLISWEB';
    private const MAGASIN = 'MAGASIN';
    private const SPRINGARE = 'SPRINGARE';
    private const TRUSK = 'TRUSK';
    private readonly ObjectRepository $fastmagRepository;

    public function __construct(
        private readonly ManagerRegistry $emRegistry,
        private readonly Factory $smFactory,
        private readonly FactoryInterface $orderFactory,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly FactoryInterface $customerFactory,
        private readonly CustomerRepository $customerRepository,
        private readonly FactoryInterface $addressFactory,
        private readonly PaymentMethodRepositoryInterface $paymentMethodRepository,
        private readonly ShippingMethodRepositoryInterface $shippingMethodRepository,
        private readonly FactoryInterface $paymentFactory,
        private readonly ChannelRepository $channelRepository,
        private readonly ProductVariantRepositoryInterface $productVariantRepository,
        private readonly FactoryInterface $orderItemFactory,
        private readonly FactoryInterface $shipmentFactory,
        private readonly EntityManagerInterface $managerOrder,
        private readonly OrderItemQuantityModifierInterface $orderQuantityModifier,
        private readonly FactoryInterface $adjustmentFactory,
        private readonly MailerInterface $mailer,
        private readonly SellerRepository $sellerRepository,
        private readonly StoreRepository $storeRepository,
        private readonly FamiliarityRepositoryInterface $familiarityRepository,
        private readonly CliBasedChannelContext $cliBasedChannelContext,
        private readonly ReportingInterface $reporting,
        private readonly EmailConfigurationRegistryInterface $emailConfigurationRegistry
    ) {
        $this->fastmagRepository = $this->emRegistry->getManager()->getRepository(Fastmag::class);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws EmailConfigurationNotFoundException
     */
    public function process(): void
    {
        $logs['ok'] = $logs['errors'] = [];
        $countriesJson = json_decode(file_get_contents(__DIR__ . '/../../vendor/symfony/intl/Resources/data/regions/fr.json'));

        $connection = $this->emRegistry->getManager('fastmag')->getConnection();

        $sqlClientOrder = "SELECT ventes.Vente, ventes.Date, ventes.Heure, ventes.Total, ventes.CodeMag, ventes.Vendeur, ventes.AdrLivraison as venteAdrLivraison,
                     clients.Nom, clients.Prenom, clients.Adresse1, clients.Adresse2, clients.codepostal, clients.Ville, clients.Pays, clients.Telephone, clients.Portable, clients.Email, clients.JourNaissance, clients.moisNaissance, clients.AnneeNaissance, clients.DateCreation, clients.PubParEMail, clients.MagasinCreation, clients.Provenance,
                     clientadresse.AdrLivraison, clientadresse.Nom as nomLivraison, clientadresse.Adresse1 as AdresseLivraison1, clientadresse.Adresse2 as AdresseLivraison2, clientadresse.codepostal as AdresseLivraisonCodePostal, clientadresse.Ville as AdresseLivraisonVille, clientadresse.Pays as AdresseLivraisonPays, clientadresse.Telephone as AdresseLivraisonTelephone,
                     clientadresse.Batiment, clientadresse.Digicode1, clientadresse.Digicode2, clientadresse.Interphone, clientadresse.Etage, clientadresse.Porte, clientadresse.Escalier
              FROM ventes
              left join clients on ventes.Client = clients.Client
              left join clientadresse on clients.Client = clientadresse.Client
              WHERE ventes.Nature = 'VENTE' and (ventes.AdrLivraison = '' OR ventes.AdrLivraison =  clientadresse.AdrLivraison) "; // test with 27 (3) 31 32

        $sqlClientOrder .= 'and ventes.Date = CURRENT_DATE and ventes.Heure > hour(now()) - 2';
        $stmt = $connection->prepare($sqlClientOrder);

        $stmt->execute();

        $fastmagOrders = [];

        while ($sqlData = $stmt->fetch()) {
            if (!in_array($sqlData['Vente'], $fastmagOrders)) {
                $fastmagOrders[$sqlData['Vente']] = [];
            }

            $fastmagOrders[$sqlData['Vente']][] = $sqlData;
        }

        $channel = $this->channelRepository->findOneByCode(ChannelCode::CHANNEL_RETAIL);
        $this->cliBasedChannelContext->setChannel($channel);
        $phoneNumber = null;
        $stateMachinePayment = null;
        $storeName = null;
        foreach ($fastmagOrders as $fastmagOrderFull) {
            try {
                $customerLog = '';
                $fastmagOrder = $store = null;

                // security to ignore other clientadresses
                foreach ($fastmagOrderFull as $fastmagOrderKey => $fastmagOrderShipping) {
                    if ($fastmagOrderShipping['venteAdrLivraison'] && $fastmagOrderShipping['venteAdrLivraison'] !== $fastmagOrderShipping['AdrLivraison']) {
                        unset($fastmagOrderFull[$fastmagOrderKey]);
                    }
                }

                if (1 === count($fastmagOrderFull)) {
                    $fastmagOrder = $fastmagOrderFull[0];
                    $store = $this->storeRepository->findOneByName($fastmagOrder['CodeMag']);
                } else {
                    if (isset($fastmagOrderFull[0])) {
                        $store = $this->storeRepository->findOneByName($fastmagOrderFull[0]['CodeMag']);
                    }
                }
                $storeName = (!empty($store) && !empty($store->getName())) ? $store->getName() . ' - ' : '';
                if (null === $fastmagOrder) {
                    $logs['errors'][] = $storeName . 'vente n° ' . $fastmagOrderFull[0]['Vente'] . " Impossible de définir l'adresse de livraison (plusieurs associées au client et aucune défini)";
                    continue;
                }

                if (!$store) {
                    $store = $this->storeRepository->findOneById(5); // STE_CROIX

                    $this->reporting->reportIssue(
                        'Import Fastmag : Shop is not filled',
                        Sentry\Severity::warning(),
                        ['fastmag' => $fastmagOrder['Vente']],
                        ['payload' => $store]
                    );
                }

                $fastmag_exist = $this->fastmagRepository->findOneBy(['fastmagId' => $fastmagOrder['Vente']]);

                if ($fastmag_exist) {
                    $logs['ok'][] = 'Commande ' . $fastmagOrder['Vente'] . ' déjà intégrée';
                    continue;
                }

                /** @var Order $order */
                $order = $this->orderFactory->createNew();
                $order->setNotes('FastMagId: ' . $fastmagOrder['Vente']);

                $order->setChannel($channel);

                if (!$fastmagOrder['Email']) {
                    $customer = $this->customerRepository->findOneByEmail('boutique@tediber.com');
                    $customerLog = '</td><td>sans données client';
                } else {
                    $customer = $this->customerRepository->findOneByEmail($fastmagOrder['Email']);

                    if (!$customer) {
                        $customer = new Customer();
                        $this->customerFactory->createNew();
                    }

                    $customer->setEmail($fastmagOrder['Email']);
                    $customer->setFirstName($fastmagOrder['Prenom']);
                    $customer->setLastName($fastmagOrder['Nom']);
                    $customer->setReferral($fastmagOrder['MagasinCreation']);
                    $customer->setLocale('fr_FR');
                    // $customer->setDefaultAddress();

                    if ($fastmagOrder['JourNaissance'] > 0 && $fastmagOrder['moisNaissance'] > 0 && $fastmagOrder['AnneeNaissance'] > 1900) {
                        $birthDate = \DateTime::createFromFormat('d/m/Y', $fastmagOrder['JourNaissance'] . '/' . $fastmagOrder['moisNaissance'] . '/' . $fastmagOrder['AnneeNaissance']);
                        $customer->setBirthday($birthDate);
                    }

                    $customer->setCreatedAt(\DateTime::createFromFormat('Y-m-d', $fastmagOrder['DateCreation']));
                    $customer->setSubscribedToNewsletter($fastmagOrder['PubParEMail']); // etiqueter pour mailchimp à voir

                    $phoneNumber = ('' !== $fastmagOrder['Portable']) ? $fastmagOrder['Portable'] : $fastmagOrder['Telephone'];
                    $customer->setPhoneNumber($phoneNumber);

                    if ($familiarity = $this->familiarityRepository->findOneByCode($fastmagOrder['Provenance'])) {
                        $customer->addFamiliarity($familiarity);
                    }

                    $this->customerRepository->add($customer);

                    $customerLog = '</td><td>' . $fastmagOrder['Prenom'];
                    $customerLog .= '</td><td>' . $fastmagOrder['Nom'];
                    $customerLog .= '</td><td>' . $fastmagOrder['Email'];
                    $customerLog .= '</td><td>' . $phoneNumber;
                }

                $order->setCheckoutCompletedAt(new \DateTime($fastmagOrder['Date'] . ' ' . $fastmagOrder['Heure']));
                $order->setCustomer($customer);
                $order->setBis(0);

                $order->setCurrencyCode('EUR');
                $order->setLocaleCode('fr_FR');

                $stateMachineCheckout = $this->smFactory->get($order, OrderCheckoutTransitions::GRAPH);

                // find CountryCode by french name
                $billingCountryCode = $shippingCountryCode = 'FR';
                foreach ($countriesJson->Names as $countryCode => $countryName) {
                    if (0 === strcasecmp((string) $countryName, (string) $fastmagOrder['Pays'])) {
                        $billingCountryCode = $countryCode;
                    }
                    if (0 === strcasecmp((string) $countryName, (string) $fastmagOrder['AdresseLivraisonPays'])) {
                        $shippingCountryCode = $countryCode;
                    }
                }

                if ($fastmagOrder['AdresseLivraison1']) {
                    $shippingAddress = $this->addressFactory->createNew();

                    $shippingName = explode(' ', (string) $fastmagOrder['nomLivraison']);

                    if (count($shippingName) > 2) {
                        $shippingAddress->setFirstName($shippingName[0] . ' ' . $shippingName[1]);
                        unset($shippingName[0]);
                        unset($shippingName[1]);
                        $shippingAddress->setLastName(implode(' ', $shippingName));
                    } elseif (2 === count($shippingName)) {
                        $shippingAddress->setFirstName($shippingName[0]);
                        $shippingAddress->setLastName($shippingName[1]);
                    } elseif (1 === count($shippingName)) {
                        $shippingAddress->setFirstName($shippingName[0]);
                        $shippingAddress->setLastName($shippingName[0]);
                    }

                    $shippingAddress->setStreet($fastmagOrder['AdresseLivraison1'] . "\n" . $fastmagOrder['AdresseLivraison2']);
                    $shippingAddress->setPostcode($fastmagOrder['AdresseLivraisonCodePostal']);

                    $shippingAddress->setCity($fastmagOrder['AdresseLivraisonVille']);

                    $shippingAddress->setCountryCode($shippingCountryCode);
                    $shippingAddress->setPhoneNumber($phoneNumber); // unused phone $fastmagOrder['AdresseLivraisonTelephone']

                    $details = '';
                    if ($fastmagOrder['Batiment']) {
                        $details .= 'Batiment ' . $fastmagOrder['Batiment'];
                    }
                    if ($fastmagOrder['Digicode1']) {
                        $details .= 'Digicode1 ' . $fastmagOrder['Digicode1'];
                    }
                    if ($fastmagOrder['Digicode2']) {
                        $details .= 'Digicode2 ' . $fastmagOrder['Digicode2'];
                    }
                    if ($fastmagOrder['Interphone']) {
                        $details .= 'Interphone ' . $fastmagOrder['Interphone'];
                    }
                    if ($fastmagOrder['Etage']) {
                        $details .= 'Etage ' . $fastmagOrder['Etage'];
                    }
                    if ($fastmagOrder['Porte']) {
                        $details .= 'Porte ' . $fastmagOrder['Porte'];
                    }
                    if ($fastmagOrder['Escalier']) {
                        $details .= 'Escalier ' . $fastmagOrder['Escalier'];
                    }

                    $shippingAddress->setDetails($details);

                    $shippingAddress->setCustomer($customer);
                    $order->setShippingAddress($shippingAddress);
                } else {
                    $shippingAddress = $this->addressFactory->createNew();

                    $shippingAddress->setFirstName($customer->getFirstName()); // $fastmagOrder['Prenom']
                    $shippingAddress->setLastName($customer->getLastName()); // $fastmagOrder['Nom']);

                    if ($fastmagOrder['Pays']) {
                        $shippingAddress->setStreet($fastmagOrder['Adresse1'] . "\n" . $fastmagOrder['Adresse2']);
                        $shippingAddress->setCity($fastmagOrder['Ville']);
                        $shippingAddress->setPostcode($fastmagOrder['codepostal']);
                        $shippingAddress->setCountryCode($billingCountryCode);
                        $shippingAddress->setPhoneNumber($phoneNumber);
                    } else {
                        $shippingAddress->setStreet('Boutique');
                        $shippingAddress->setCity('Paris');
                        $shippingAddress->setPostcode(75004);
                        $shippingAddress->setCountryCode('FR');
                    }

                    $shippingAddress->setCustomer($customer);

                    $order->setShippingAddress($shippingAddress);
                }

                $billingAddress = $this->addressFactory->createNew();

                $billingAddress->setFirstName($customer->getFirstName());
                $billingAddress->setLastName($customer->getLastName());

                if ($fastmagOrder['Pays']) {
                    $billingAddress->setStreet($fastmagOrder['Adresse1'] . "\n" . $fastmagOrder['Adresse2']);
                    $billingAddress->setCity($fastmagOrder['Ville']);
                    $billingAddress->setPostcode($fastmagOrder['codepostal']);
                    $billingAddress->setCountryCode($billingCountryCode);
                    $billingAddress->setPhoneNumber($phoneNumber);
                } else {
                    $billingAddress->setStreet('Boutique');
                    $billingAddress->setCity('Paris');
                    $billingAddress->setPostcode(75004);
                    $billingAddress->setCountryCode('FR');
                }

                $billingAddress->setCustomer($customer);

                $order->setBillingAddress($billingAddress);

                $stateMachineCheckout->apply(OrderCheckoutTransitions::TRANSITION_ADDRESS);

                // ADD PRODUCTS Lines

                $sqlLinesOrder = 'SELECT lignes.vente, lignes.Famille, lignes.BarCode, lignes.Prix, lignes.quantite, lignes.remise, lignes.total, lignes.Designation2, lignes.Motif,
                         lignescommentaire.Commentaire
                  FROM lignes
                  LEFT JOIN lignescommentaire on lignes.ligne = lignescommentaire.IDLIgne
                  WHERE lignes.vente = :idVente'; // ORDER BY ventes.Vente DESC

                $linestmt = $connection->prepare($sqlLinesOrder);
                $linestmt->bindParam('idVente', $fastmagOrder['Vente']);
                $linestmt->execute();

                $fastmagOrderFullLines = $linestmt->fetchAll();

                $packs = [];
                $shipmentFlag = false;

                foreach ($fastmagOrderFullLines as $fastmagOrderItem) {
                    $packFlag = 0;
                    $code = $fastmagOrderItem['BarCode'];

                    if ('LIVRAISON' === $code) {
                        $shipmentFlag = true;
                        $stateMachineShipping = $this->smFactory->get($order, OrderShippingTransitions::GRAPH);
                        $stateMachineShipping->apply(OrderShippingTransitions::TRANSITION_REQUEST_SHIPPING);

                        $shippingDetails = json_decode((string) $fastmagOrderItem['Commentaire']);

                        if (!$shippingDetails) {
                            $logs['errors'][] = $storeName . 'Commande ' . $fastmagOrder['Vente'] . ' code de livraison manquant' . $customerLog;
                            continue;
                        }

                        $shippingMethod = $this->shippingMethodRepository->findOneBy(['code' => $shippingDetails->method]);

                        $shipment = $this->shipmentFactory->createNew();
                        $shipment->setMethod($shippingMethod);

                        $shipment->setStartDeliverySlot(new \DateTime($shippingDetails->start_slot));
                        $shipment->setEndDeliverySlot(new \DateTime($shippingDetails->end_slot));
                        $shipment->setIdShippingMethodBalou($shippingDetails->erp_id);

                        $order->addShipment($shipment);
                    } elseif (in_array($code, [self::COLISWEB, self::MAGASIN, self::SPRINGARE, self::TRUSK])) {
                        $shipmentFlag = OrderShippingStates::STATE_SHIPPED;
                        $stateMachineShipping = $this->smFactory->get($order, OrderShippingTransitions::GRAPH);
                        $stateMachineShipping->apply(OrderShippingTransitions::TRANSITION_REQUEST_SHIPPING);

                        $shippingMethod = $this->shippingMethodRepository->findOneBy(['code' => $code]);

                        $shipment = $this->shipmentFactory->createNew();
                        $shipment->setMethod($shippingMethod);

                        $shipment->setStartDeliverySlot(new \DateTime());
                        $shipment->setEndDeliverySlot(new \DateTime());
                        $shipment->setShippingDate(new \DateTime());
                        $shipment->setIdShippingMethodBalou(self::MAGASIN === $code ? 1 : 2);
                        $shipment->setState(OrderShippingStates::STATE_SHIPPED);

                        $order->addShipment($shipment);
                    } else {
                        // for PACKS (bed-frame)
                        if (array_key_exists($fastmagOrderItem['Motif'], $packs) && $packs[$fastmagOrderItem['Motif']]) {
                            $code = $packs[$fastmagOrderItem['Motif']]['code'] . '_' . $code;
                            $packFlag = 1;

                            if ($packs[$fastmagOrderItem['Motif']]['quantity'] !== (int) $fastmagOrderItem['quantite']) {
                                $logs['errors'][] = $storeName . 'Commande ' . $fastmagOrder['Vente'] . ' mauvaise quantité pack (' . $packs[$fastmagOrderItem['Motif']]['quantity'] . ' ' . (int) $fastmagOrderItem['quantite'] . ' pour ' . $fastmagOrderItem['Motif'] . $customerLog;
                                continue;
                            }
                        } elseif ($fastmagOrderItem['Motif']) {
                            $packs[$fastmagOrderItem['Motif']] = [];
                            $packs[$fastmagOrderItem['Motif']]['quantity'] = (int) $fastmagOrderItem['quantite'];

                            if (1 === $packs[$fastmagOrderItem['Motif']]['quantity']) {
                                $packs[$fastmagOrderItem['Motif']]['code'] = $fastmagOrderItem['BarCode'];
                            } elseif (2 === $packs[$fastmagOrderItem['Motif']]['quantity']) {
                                $packs[$fastmagOrderItem['Motif']]['code'] = $fastmagOrderItem['Designation2'];
                            }

                            continue;
                        }

                        $variant = $this->productVariantRepository->findOneByCode($code);

                        if (!$variant) {
                            $logs['errors'][] = $storeName . 'Commande ' . $fastmagOrder['Vente'] . ' produit non trouvé:' . $code . $customerLog;
                            continue;
                        }

                        $orderItem = $this->orderItemFactory->createNew();
                        $orderItem->setVariant($variant);

                        if ($packFlag) {
                            $orderItem->setUnitPrice((float) $fastmagOrderItem['total'] * 100 * (int) $fastmagOrderItem['quantite']);
                            $this->orderQuantityModifier->modify($orderItem, 1);
                        } else {
                            $orderItem->setUnitPrice((float) $fastmagOrderItem['Prix'] * 100);
                            $this->orderQuantityModifier->modify($orderItem, (int) $fastmagOrderItem['quantite']);
                        }

                        if (!$fastmagOrderItem['Motif'] && $fastmagOrderItem['remise']) {
                            $adjustment = $this->adjustmentFactory->createNew();
                            $adjustment->setType(AdjustmentInterface::ORDER_ITEM_PROMOTION_ADJUSTMENT);
                            $adjustment->setAmount(round((int) $fastmagOrderItem['quantite'] * $fastmagOrderItem['remise'] * $fastmagOrderItem['Prix'] * -1));
                            $adjustment->setLabel($fastmagOrderItem['remise'] . '%');
                            $orderItem->addAdjustment($adjustment);
                        }

                        foreach ($orderItem->getUnits() as $unit) {
                            $adjustmentTva = $this->adjustmentFactory->createNew();
                            $adjustmentTva->setType(AdjustmentInterface::TAX_ADJUSTMENT);
                            $adjustmentTva->setAmount(round($fastmagOrderItem['total'] * 100 / 6 / (int) $fastmagOrderItem['quantite']));
                            $adjustmentTva->setLabel('TVAFR (20%)');
                            $adjustmentTva->setNeutral(true);
                            $unit->addAdjustment($adjustmentTva);
                        }

                        $order->addItem($orderItem);
                    }
                }

                if (!$shipmentFlag) {
                    $logs['errors'][] = $storeName . 'Aucun mode de livraison prévu pour cette commande : ' . $fastmagOrder['Vente'];
                    continue;
                }

                // ADD PRODUCTS Lines

                $sqlPaymentsOrder = 'SELECT reglements.Montant, reglements.Echeance, reglements.RefPaiement, reglements.Libelle
                  FROM reglements
                  WHERE reglements.vente = :idVente'; // ORDER BY ventes.Vente DESC

                $paymentsstmt = $connection->prepare($sqlPaymentsOrder);
                $paymentsstmt->bindParam('idVente', $fastmagOrder['Vente']); // bind_param("s", $fastmagOrder['Vente']);
                $paymentsstmt->execute();

                $fastmagOrderFullPayments = $paymentsstmt->fetchAll();

                if (count($fastmagOrderFullPayments) > 0) {
                    $stateMachinePayment = $this->smFactory->get($order, OrderPaymentTransitions::GRAPH);
                    $stateMachinePayment->apply(OrderPaymentTransitions::TRANSITION_REQUEST_PAYMENT);
                }

                foreach ($fastmagOrderFullPayments as $fastmagOrderPayment) {
                    $paymentMethod = $this->paymentMethodRepository->findOneBy(['code' => $fastmagOrderPayment['Libelle']]);

                    if (!$paymentMethod) {
                        $paymentMethod = $this->paymentMethodRepository->findOneBy(['code' => 'other']);
                    }

                    $payment = $this->paymentFactory->createNew();
                    $payment->setMethod($paymentMethod);
                    $payment->setCurrencyCode('EUR');
                    $payment->setAmount($fastmagOrderPayment['Montant'] * 100);
                    $payment->setDetails([$fastmagOrderPayment['Libelle'], $fastmagOrderPayment['RefPaiement'], 'PaymentDate: ' . $fastmagOrderPayment['Echeance']]); // prendre comme payum
                    $order->addPayment($payment);
                }

                $seller = $this->sellerRepository->findOneByName($fastmagOrder['Vendeur']);

                $orderStore = new OrderStore();

                if ($seller) {
                    $orderStore->setSeller($seller);
                }

                $orderStore->setStore($store);
                $orderStore->setOrder($order);

                $order->setOrderStore($orderStore);

                $this->orderRepository->add($order);

                $fastmag = new Fastmag();
                $fastmag->setFastmagId((int) $fastmagOrder['Vente']);
                $fastmag->setOrder($order);
                $fastmag->setSeller($fastmagOrder['Vendeur']);
                $order->setFastmag($fastmag);

                $this->managerOrder->flush();
                // also trigger event to send to erp
                $stateMachineCheckout->apply(OrderCheckoutTransitions::TRANSITION_COMPLETE);
                if ($order->hasPayments()) {
                    $stateMachinePayment->apply(OrderPaymentTransitions::TRANSITION_PAY);

                    $statePayment = $this->smFactory->get($order->getPayments()[0], PaymentTransitions::GRAPH);
                    $statePayment->apply(PaymentTransitions::TRANSITION_COMPLETE);
                }

                $this->managerOrder->flush();

                $logs['ok'][] = 'Commande ' . $fastmagOrder['Vente'] . ' intégré';
            } catch (\Exception $exception) {
                $message = $storeName . 'Commande ' . $fastmagOrder['Vente'] . ' erreurs:' . $exception->getMessage() . $customerLog;
                $logs['errors'][] = $message;
                $this->reporting->reportIssue(
                    $message,
                    Sentry\Severity::error(),
                    ['fastmag' => $fastmagOrder['Vente']]
                );
                continue;
            }
        }

        if (count($logs['errors']) > 0) {
            $textMessage = '';
            foreach ($logs['errors'] as $errorMessage) {
                $textMessage .= sprintf('<tr><td>%s</td></tr>', $errorMessage);
            }

            $orderIntegrationErrorAddresses = $this->emailConfigurationRegistry->getEmailConfigurationByName(EmailConfigurationName::ORDER_INTEGRATION_ERROR);
            $message = (new Email())
                ->subject('Fastmag/Sylius erreur intégration commandes')
                ->from(new Address('support@tediber.com', 'TEDIBER Sylius'))
                ->to(...$orderIntegrationErrorAddresses->getPrincipals())
                ->cc(...$orderIntegrationErrorAddresses->getCcs())
                ->html($textMessage);

            $this->mailer->send($message);
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Doctrine\Entity\Sylius\Address;
use App\Matcher\Addressing\ZoneMatcher;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Sylius\Component\Core\Model\Scope;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ZoneController extends ResourceController
{
    public function testAction(Request $request): Response
    {
        $zones = null;

        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        $form = $this->createForm(type: $configuration->getFormType(), options: $configuration->getFormOptions());
        $form->handleRequest($request);

        if ($request->isMethod(Request::METHOD_POST) && $form->isSubmitted() && $form->isValid()) {
            /** @var ZoneMatcher $zoneMatcher */
            $zoneMatcher = $this->get('sylius.zone_matcher');

            /** @var Address $data */
            $data = $form->getData();

            $zones = $zoneMatcher->matchAll($data, Scope::SHIPPING);
        }

        return $this->render('@App/Admin/Zone/zoneTest.html.twig', [
            'zones' => $zones,
            'form' => $form->createView(),
        ]);
    }
}

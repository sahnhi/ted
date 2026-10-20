<?php

namespace App\Services\Transmission\Entrepot;

use App\Doctrine\Entity\Sylius\ShippingMethodInterface;
use App\Doctrine\Repository\ZoneRepository;
use App\Services\Transmission\AbstractTransmissionService;
use Doctrine\ORM\NonUniqueResultException;
use GuzzleHttp\Exception\GuzzleException;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class SpidyService extends AbstractTransmissionService
{
    protected array $normalizeArray = [];
    protected array $headerLine;

    public function __construct(
        private readonly ZoneRepository $zoneRepository,
        private readonly RepositoryInterface $zoneMemberRepository
    ) {
        parent::__construct();
    }

    /**
     * @throws GuzzleException|NonUniqueResultException
     */
    public function normalizeArray(array $order, int $numberLine): array
    {
        $this->recursiveArray($order);

        if (empty($this->normalizeArray)) {
            return [];
        }

        $orderNumber = preg_replace('/^TED/', '', (string) $this->normalizeArray['N° de commande']);

        /* @var call balou to $expeditionInfos */

        // to fix, later from sylius expedition date
        $this->normalizeArray['Numero de ligne'] = $numberLine;
        $this->normalizeArray['Prix unitaire valorisation'] = $this->normalizeArray['Prix unitaire valorisation'] > 0 ?
            str_replace('.', ',', $this->normalizeArray['Prix unitaire valorisation'] / 100) : 0;
        // precise date d'expe for Spidy
        /* Agence Vir */
        if (str_contains((string) $this->normalizeArray['Pre tournée / TRANSPORTEUR'], ShippingMethodInterface::METHOD_VIR)) {
            if (null !== $this->zoneRepository->findZoneByCpAndLocal($this->normalizeArray['Code postal'], $this->normalizeArray['Code pays'])) {
                $zone = $this->zoneRepository->findZoneByCpAndLocal($this->normalizeArray['Code postal'], $this->normalizeArray['Code pays']);
            } else {
                $zone = $this->zoneMemberRepository->getZoneMemberByCode($this->normalizeArray['Code pays'], true);
            }
            $zoneMembers = $this->zoneMemberRepository->findBy(['code' => $zone->getCode()]);
            foreach ($zoneMembers as $zoneMember) {
                if (!str_contains((string) $zoneMember->getBelongsTo()->getName(), '_') && str_contains((string) $zoneMember->getBelongsTo()->getName(), 'VIR')) {
                    $this->normalizeArray['Mention étiquette expedition'] = $zoneMember->getBelongsTo()->getName();
                }
            }
        }

        /* code produit && code transporteur */

        $this->codeProduitTransporteur($this->normalizeArray['Pre tournée / TRANSPORTEUR']);

        $array_csv = array_replace($this->headerLine, $this->normalizeArray);
        $new_line = ["\r\n", "\n", "\r"];
        foreach ($array_csv as $key => $line) {
            $line = str_replace($new_line, ' ', (string) $line);
            $array_csv[$key] = $line;
        }

        return $array_csv;
    }

    public function recursiveArray($order)
    {
        parent::recursiveArray($order);
    }

    public function codeProduitTransporteur($codeProduitTransporteur)
    {
        parent::codeProduitTransporteur($codeProduitTransporteur);
    }
}

<?php

namespace App\Services\Transmission;

abstract class AbstractTransmissionService
{
    protected array $normalizeArray = [];
    protected array $headerLine;

    /**
     * abstractTransmissionService constructor.
     */
    public function __construct()
    {
        $this->headerLine = array_fill_keys(
            [
                'Réservé SPIDY', // A
                'Société',
                'Entrepôt',
                'N° de commande',
                'Code client/Client Propriétaire',
                'Raison sociale',
                'Adresse 1',
                'Adresse 2',
                'Adresse 3',
                'Code postal',
                'Ville',
                'Code pays',
                'Initiateur de commande',
                'Commentaire 1 (transport)',
                'Commentaire 2',
                'Commentaire 3',
                'Type de commande',
                'Siecle de livraison',
                'Date prep AAMMJJ',
                'Pre tournée / TRANSPORTEUR',
                'Tour',
                'Priorité missions',
                'Type date si calcul auto priorité',
                'Type priorité',
                'Passage par portefeuille imposé',
                'Type de mouvement 1',
                'Mention étiquette expedition', // AA
                'Contre remboursement',
                'Montant contre remboursement',
                'mode de tri préparation assistée',
                'Code expediteur EDI',
                'Numero de ligne',
                'Code Produit',
                'N° de lot',
                'Qté à expedier',
                'Code unité',
                'Commentaire ligne produit',
                'Prix unitaire valorisation',
                'Adresse email erreur',
                'Adresse alternative 1',
                'Adresse alternative 2',
                'Adresse alternative 3',
                'Adresse alternative 4',
                'Adresse alternative code postal',
                'Adresse alternative ville',
                'Adresse alternative pays',
                'Code porte',
                'Code porte 2',
                'Interphone',
                'Tel portable',
                'Tel Fixe',
                'Zone complémentaire spécifique transporteur', // AZ
                'itemCode', // Commentaire commande complémentaire 1
                'Impression commentaire comp 1 sur BL',
                'Commentaire commande complémentaire 2',
                'Impression commentaire comp 2 sur BL',
                'Commentaire commande complémentaire 3',
                'Impression commentaire comp 3 sur BL',
                'Commentaire commande complémentaire 4',
                'Impression commentaire comp 4 sur BL',
                'Commentaire commande complémentaire 5',
                'Impression commentaire comp 5 sur BL',
                'Destinataire commande',
                'Type de client',
                'Identifiant 1',
                'Identifiant 2',
                'Identifiant 3',
                'Type adresse',
                'Code agence',
                'Propriétaire arrivée transfert propriété',
                'Catégorie envoi CN23', // BS
            ],
            ''
        );
        $this->headerLine['Société'] = 'SQ7';
        $this->headerLine['Entrepôt'] = 'SQ7';
        $this->headerLine['Code client/Client Propriétaire'] = 'TEDIBER';
        $this->headerLine['Siecle de livraison'] = 1;
        $this->headerLine['Type de mouvement 1'] = 1;
        $this->headerLine['Adresse email erreur'] = 'dev@tediber.com';
        $this->headerLine['Tel Fixe'] = '01 86 95 47 10';
    }

    public function normalizeArray(array $order, int $numberLine)
    {
        $this->recursiveArray($order);
        if (!empty($this->normalizeArray)) {
            return array_replace($this->headerLine, $this->normalizeArray);
        }
    }

    /**
     * @param $order multi dimension
     *
     * @return void normalizeArray[] one dimension
     */
    public function recursiveArray($order)
    {
        // on décompose chaque dimension de normalization en une seul
        foreach ($order as $key => $value) {
            if (!is_array($value)) {
                $this->normalizeArray[$key] = $value;
            } else {
                $this->recursiveArray($value);
            }
        }
    }

    public function codeProduitTransporteur($codeProduitTransporteur)
    {
        // code produit du transporteur
        $indextransporteurspidy = ['DPD' => 'DPPR', 'BR' => 'ACC', 'BRA' => 'ACC', 'BRB' => 'ACC', 'BRC' => 'ACC', 'UPS' => 'UPST', 'VELO TEDIB' => 'TEDIBER', 'JO' => 'SC18', 'XP' => 'HP18', 'E-DELIV' => 'EDELIV', 'FRANCE-EXPRESS' => 'GEXP'];

        if (array_key_exists($codeProduitTransporteur, $indextransporteurspidy)) {
            $this->normalizeArray['Pre tournée / TRANSPORTEUR'] = $indextransporteurspidy[$codeProduitTransporteur];
        }
    }

    public function getTransporteur()
    {
        $this->normalizeArray['Transporteur 2'] = 'DPD' === $this->normalizeArray['Transporteur 1'] ? 'DP' : '';

        return $this->normalizeArray['Transporteur 1'];
    }
}

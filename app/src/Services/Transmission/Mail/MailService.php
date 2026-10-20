<?php

namespace App\Services\Transmission\Mail;

use App\Services\Transmission\AbstractTransmissionService;

/**
 * Class MailService.
 */
class MailService extends AbstractTransmissionService
{
    protected array $normalizeArray;
    protected array $headerLine;

    public function __construct()
    {
        $this->headerLine = array_fill_keys(['Qté à expedier', 'Code Produit', 'Commentaire 1 (transport)', 'Raison sociale', 'Adresse 1', 'Adresse 2', 'Adresse 3', 'Code pays', 'Code postal', 'Ville', 'Tel portable', 'Tel Fixe', 'Commentaire 2'], ' ');
    }

    public function normalizeArray(array $order, int $numberLine): array
    {
        $this->recursiveArray($order);

        // Facturation or Conso adress
        $this->normalizeArray['Raison sociale'] = ' ' !== $this->normalizeArray['Raison sociale Destination'] ? $this->normalizeArray['Raison sociale Destination'] : $this->normalizeArray['Raison sociale Conso'];
        $this->normalizeArray['Adresse 1'] = ' ' !== $this->normalizeArray['Adresse 1 Desti'] || null !== $this->normalizeArray['Adresse 1 Desti'] ? $this->normalizeArray['Adresse 1 Desti'] : $this->normalizeArray['Adresse 1 Conso'];
        $this->normalizeArray['Adresse 2'] = ' ' !== $this->normalizeArray['Adresse 2 Desti'] || null !== $this->normalizeArray['Adresse 2 Desti'] ? $this->normalizeArray['Adresse 2 Desti'] : $this->normalizeArray['Adresse 2 Conso'];
        $this->normalizeArray['Code postal'] = ' ' !== $this->normalizeArray['Code postal Desti'] ? $this->normalizeArray['Code postal Desti'] : $this->normalizeArray['Code postal Conso'];
        $this->normalizeArray['Ville'] = ' ' !== $this->normalizeArray['Ville Desti'] ? $this->normalizeArray['Ville Desti'] : $this->normalizeArray['Ville Conso'];
        $this->normalizeArray['Code pays'] = ' ' !== $this->normalizeArray['Code pays desti'] ? $this->normalizeArray['Code pays desti'] : $this->normalizeArray['Code pays conso'];
        $this->normalizeArray['infos'] = $this->normalizeArray['Adresse 3'] ?? '';

        switch ($this->normalizeArray['ref']) {
            case 5:
                $livannee = \date('Y');
                $search_livimp = ['LIV IMP LE', ' de', 'à', 'a'];
                $replace_livimp = ['', "/{$livannee} tra le", 'e le', 'e le'];
                $this->normalizeArray['Commentaire 1 (transport)'] = str_replace($search_livimp, $replace_livimp, (string) $this->normalizeArray['Commentaire 1 (transport)']);
                $this->normalizeArray['Commentaire ligne produit'] = str_replace('MATELAS', 'MATERASSO', (string) $this->normalizeArray['Commentaire ligne produit']);
                break;

            case 6:
                $livannee = \date('Y');
                $search_livimp = ['LIV IMP LE', ' de', 'à', 'a'];
                $replace_livimp = ['', "/{$livannee} entre las", 'y las', 'y las'];
                $this->normalizeArray['Commentaire 1 (transport)'] = str_replace($search_livimp, $replace_livimp, (string) $this->normalizeArray['Commentaire 1 (transport)']);
                $this->normalizeArray['Commentaire ligne produit'] = str_replace('MATELAS', 'COLCHON', (string) $this->normalizeArray['Commentaire ligne produit']);
                break;

            default:
                $livannee = \date('Y');
                $search_livimp = ['LIV IMP LE', ' de', 'à', 'a'];
                $replace_livimp = ['', "/{$livannee} entre", 'et', 'et'];
                $this->normalizeArray['Commentaire 1 (transport)'] = str_replace($search_livimp, $replace_livimp, (string) $this->normalizeArray['Commentaire 1 (transport)']);
        }

        $array_mail[$this->normalizeArray['N° de commande']] = [
            'datelivraison' => $this->normalizeArray['Commentaire 1 (transport)'],
            'nocommande' => $this->normalizeArray['N° de commande'],
            'consommateur' => $this->normalizeArray['Raison sociale'],
            'adresse' => $this->normalizeArray['Adresse 1'] . $this->normalizeArray['Adresse 2'],
            'zone' => $this->normalizeArray['Code postal'] . $this->normalizeArray['Ville'] . $this->normalizeArray['Code pays'],
            'infos' => $this->normalizeArray['infos'],
            'telephone' => $this->normalizeArray['Tel portable'],
            'produit' => $this->normalizeArray['Commentaire ligne produit'],
            'quantite' => $this->normalizeArray['Qté à expedier'],
            'adresse2' => $this->normalizeArray['Adresse 2'],
            'codepostal' => $this->normalizeArray['Code postal'],
            'ville' => $this->normalizeArray['Ville'],
            'pays' => $this->normalizeArray['Code pays'],
            'mail' => $this->normalizeArray['Commentaire 2'],
            ];

        return $array_mail[$this->normalizeArray['N° de commande']];
    }
}

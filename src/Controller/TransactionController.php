<?php

namespace App\Controller;

use JsonException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TransactionController extends AbstractController
{
    /**
     * @throws JsonException
     */
    #[Route('/transaction', name: 'app_transaction')]
    public function index(): Response
    {
        $argv =[''];
        $amountFixed = 1;
        foreach (explode("\n", file_get_contents($argv[1])) as $row) {

            if (empty($row)) {
                break;
            }
            $p = explode(",",$row);
            $p2 = explode(':', $p[0]);
            $value[0] = trim($p2[1], '"');
            $p2 = explode(':', $p[1]);
            $value[1] = trim($p2[1], '"');
            $p2 = explode(':', $p[2]);
            $value[2] = trim($p2[1], '"}');

            $binResults = file_get_contents('https://lookup.binlist.net/' .$value[0]);
            if (!$binResults) {
                die('error!');
            }
            $r = json_decode($binResults, false, 512, JSON_THROW_ON_ERROR);
            $isEu = isEu($r->country->alpha2);
            $rateApi = $this->getParameter('rate_api');
            $rate = @json_decode(file_get_contents($rateApi), true, 512, JSON_THROW_ON_ERROR)['rates'][$value[2]];
            if ($value[2] === 'EUR' || $rate === 0) {
                $amountFixed = $value[1];
            }
            if ($value[2] !== 'EUR' || $rate > 0) {
                $amountFixed = $value[1] / $rate;
            }

            echo $amountFixed * ($isEu === 'yes' ? 0.01 : 0.02);
            print "\n";
        }

        function isEu($c): string
        {
            $result = false;
            switch($c) {
                case 'AT':
                case 'BE':
                case 'BG':
                case 'CY':
                case 'CZ':
                case 'DE':
                case 'DK':
                case 'EE':
                case 'ES':
                case 'FI':
                case 'FR':
                case 'GR':
                case 'HR':
                case 'HU':
                case 'IE':
                case 'IT':
                case 'LT':
                case 'LU':
                case 'LV':
                case 'MT':
                case 'NL':
                case 'PO':
                case 'PT':
                case 'RO':
                case 'SE':
                case 'SI':
                case 'SK':
                    return 'yes';
                default:
                    $result = 'no';
            }
            return $result;
        }
        return $this->json([
           'controller' => true,
           'status' => 200
        ]);
    }
}

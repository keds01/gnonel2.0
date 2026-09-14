<?php

namespace App;

use App\User;
use GuzzleHttp\Client;

class PayGateGlobalHelper
{
    private static $apiKey = '1057a758-b563-42df-8bce-01456795187b';
    private static $baseUrl = 'https://paygateglobal.com';

    /**
     * Génère un lien de paiement via PayGateGlobal (Méthode 2 - Redirection)
     */
    public static function generatePaymentLink($transactionId, $amount, $description, $returnUrl, $cancelUrl, User $user)
    {
        // Construire l'URL de paiement avec les paramètres
        $params = [
            'token' => self::$apiKey,
            'amount' => $amount,
            'description' => $description,
            'identifier' => $transactionId,
            'url' => "https://gnonel.com/return-subscription", // URL de retour après paiement
            'phone' => $user->telephone ?: '',
        ];

        // Générer l'URL complète
        $paymentUrl = self::$baseUrl . '/v1/page?' . http_build_query($params);

        return [
            'data' => [
                'payment_url' => $paymentUrl,
                'tx_reference' => $transactionId
            ],
            'code' => 0
        ];
    }

    /**
     * Vérifie l'état d'une transaction via PayGateGlobal (Méthode avec identifier)
     */
    public static function checkTransaction($transactionId)
    {
        $client = new Client([
            'timeout' => 30,
            'verify' => false,
        ]);

        $payload = [
            'auth_token' => self::$apiKey,
            'identifier' => $transactionId,
        ];

        try {
            $response = $client->post(self::$baseUrl . '/api/v2/status', [
                'json' => $payload
            ]);

            $data = json_decode($response->getBody(), true);

            // Mapper les codes de statut PayGateGlobal vers le format CinetPay
            $statusMapping = [
                '0' => '00', // Paiement réussi
                '2' => '01', // En cours
                '4' => '02', // Expiré
                '6' => '03', // Annulé
            ];

            $cinetpayCode = $statusMapping[$data['status']] ?? '99';

            return [
                'code' => $cinetpayCode,
                'message' => $data['status'] == '0' ? 'Paiement réussi' : 'Paiement non réussi',
                'data' => [
                    'amount' => $data['amount'] ?? null,
                    'payment_method' => $data['payment_method'] ?? null,
                    'payment_reference' => $data['payment_reference'] ?? null,
                    'datetime' => $data['datetime'] ?? null,
                    'tx_reference' => $data['tx_reference'] ?? null,
                    'identifier' => $data['identifier'] ?? null,
                    'status' => $data['status'] ?? null,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => '99',
                'message' => 'Erreur: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Alternative: Vérifier via tx_reference (Méthode v1)
     */
    public static function checkTransactionByReference($txReference)
    {
        $client = new Client([
            'timeout' => 30,
            'verify' => false,
        ]);

        $payload = [
            'auth_token' => self::$apiKey,
            'tx_reference' => $txReference,
        ];

        try {
            $response = $client->post(self::$baseUrl . '/api/v1/status', [
                'json' => $payload
            ]);

            $data = json_decode($response->getBody(), true);

            $statusMapping = [
                '0' => '00',
                '2' => '01',
                '4' => '02',
                '6' => '03',
            ];

            $cinetpayCode = $statusMapping[$data['status']] ?? '99';

            return [
                'code' => $cinetpayCode,
                'message' => $data['status'] == '0' ? 'Paiement réussi' : 'Paiement non réussi',
                'data' => [
                    'amount' => $data['amount'] ?? null,
                    'payment_method' => $data['payment_method'] ?? null,
                    'payment_reference' => $data['payment_reference'] ?? null,
                    'datetime' => $data['datetime'] ?? null,
                    'tx_reference' => $data['tx_reference'] ?? null,
                    'identifier' => $data['identifier'] ?? null,
                    'status' => $data['status'] ?? null,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => '99',
                'message' => 'Erreur: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }
}

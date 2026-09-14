<?php

namespace App;

use App\User;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class FedaPayHelper
{
    private static function getApiKey()
    {
        $apiKey = env('FEDAPAY_SECRET_KEY', '');
        if (empty($apiKey)) {
            Log::error('FedaPay - Clé API non configurée. Ajoutez FEDAPAY_SECRET_KEY dans .env');
        }
        return $apiKey;
    }

    /**
     * Vérifie si la configuration FedaPay est valide
     */
    public static function isConfigured()
    {
        return !empty(env('FEDAPAY_SECRET_KEY', ''));
    }

    private static function getEnvironment()
    {
        return env('FEDAPAY_ENVIRONMENT', 'sandbox'); // 'sandbox' ou 'live'
    }

    public static function getBaseUrl()
    {
        $env = self::getEnvironment();
        return $env === 'live'
            ? 'https://api.fedapay.com/v1'
            : 'https://sandbox-api.fedapay.com/v1';
    }

    /**
     * Crée un client dans FedaPay ou récupère un client existant
     */
    public static function createCustomer(User $user)
    {
        // Vérifier d'abord si le client existe déjà avec les mêmes données
        $existingCustomerId = self::getCustomerByEmail($user->email);
        if ($existingCustomerId) {
            Log::info('FedaPay - Customer existant trouvé pour email ' . $user->email . ': ' . $existingCustomerId);
            
            // Vérifier si les données du client existant correspondent à l'utilisateur actuel
            $existingCustomerData = self::getCustomerData($existingCustomerId);
            if ($existingCustomerData && 
                strtolower($existingCustomerData['firstname']) === strtolower($user->prenom ?: $user->name) &&
                strtolower($existingCustomerData['lastname']) === strtolower($user->name) &&
                strtolower($existingCustomerData['email']) === strtolower($user->email)) {
                Log::info('FedaPay - Customer existant avec données identiques, réutilisation: ' . $existingCustomerId);
                return [
                    'success' => true,
                    'customer_id' => $existingCustomerId,
                    'existing' => true
                ];
            } else {
                Log::info('FedaPay - Customer existant mais données différentes, création nouveau client pour: ' . $user->email);
                // Continuer vers la création d'un nouveau client
            }
        }

        $client = new Client([
            'timeout' => 30,
            'verify' => false,
        ]);

        $payload = [
            'firstname' => $user->prenom ?: $user->name,
            'lastname' => $user->name,
            'email' => $user->email,
        ];

        // Ajouter le numéro de téléphone uniquement s'il est valide
        $phoneData = self::parsePhoneNumber($user->telephone);
        if ($phoneData && strlen($phoneData['number']) >= 8) {
            $payload['phone_number'] = [
                'number' => $phoneData['number'],
                'country' => $phoneData['country']
            ];
        }

        Log::info('FedaPay - Creating customer with payload: ' . json_encode($payload));

        try {
            $response = $client->post(self::getBaseUrl() . '/customers', [
                'json' => $payload,
                'headers' => [
                    'Authorization' => 'Bearer ' . self::getApiKey(),
                    'Content-Type' => 'application/json',
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            Log::info('FedaPay - Customer created: ' . json_encode($data));

            // FedaPay retourne la structure: {"v1/customer": {"id": ..., ...}}
            $customerId = $data['v1/customer']['id'] ?? $data['id'] ?? null;

            if (!$customerId) {
                Log::error('FedaPay - Impossible d\'extraire l\'ID client de la réponse: ' . json_encode($data));
            }

            return [
                'success' => true,
                'customer_id' => $customerId,
                'data' => $data
            ];

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $body = $response ? $response->getBody()->getContents() : 'No response body';
            Log::error('FedaPay - Create customer error (ClientException): ' . $e->getMessage());
            Log::error('FedaPay - Response body: ' . $body);

            // Vérifier si l'erreur est "email déjà utilisé"
            $bodyData = json_decode($body, true);
            if (isset($bodyData['errors']['email']) && in_array("n'est pas disponible", $bodyData['errors']['email'])) {
                Log::info('FedaPay - Email déjà utilisé, tentative de récupération du customer...');
                $existingId = self::getCustomerByEmail($user->email);
                if ($existingId) {
                    return [
                        'success' => true,
                        'customer_id' => $existingId,
                        'existing' => true
                    ];
                }
            }

            return [
                'success' => false,
                'error' => 'Erreur API FedaPay: ' . $e->getMessage() . ' - ' . $body
            ];
        } catch (\Exception $e) {
            Log::error('FedaPay - Create customer error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Génère un lien de paiement via FedaPay
     *
     * @param  string  $transactionId  Référence métier (ex. idsouscription_date_user) — FedaPay peut renvoyer une autre `reference` (trx_…) dans les webhooks
     * @param  int|null  $idsouscription  Métadonnée optionnelle pour le dashboard FedaPay (non fiable dans le webhook).
     */
    public static function generatePaymentLink($transactionId, $amount, $description, $returnUrl, $cancelUrl, User $user, $idsouscription = null)
    {
        $client = new Client([
            'timeout' => 30,
            'verify' => false,
        ]);

        // Créer d'abord le client
        $customerResult = self::createCustomer($user);
        if (!$customerResult['success']) {
            return [
                'error' => 'Erreur création client: ' . $customerResult['error'],
                'code' => 500
            ];
        }

        $customerId = $customerResult['customer_id'];

        // Créer la transaction (montant brut sans frais - frais absorbés par nous)
        $payload = [
            'description' => $description,
            'amount' => (int) $amount, // Montant exact à payer, sans ajout de frais
            'currency' => ['iso' => 'XOF'],
            'callback_url' => $returnUrl,
            'cancel_url' => $cancelUrl,
            'customer' => ['id' => $customerId],
            'reference' => $transactionId,
            'charge_fees_on' => 'customer', // Les frais sont à la charge du client (nous), pas de l'utilisateur
        ];

        // Métadonnée utile pour le dashboard FedaPay ; le webhook ne la renvoie en pratique pas — liaison via entity.id en base (AbonnementController).
        if ($idsouscription !== null && $idsouscription !== '') {
            $payload['metadata'] = [
                'gnonel_idsouscription' => (string) $idsouscription,
            ];
        }

        Log::info('FedaPay - Montant envoyé (brut sans frais): ' . $amount . ' XOF');
        Log::info('FedaPay - Payload transaction: ' . json_encode($payload));
        Log::info('FedaPay - Base URL: ' . self::getBaseUrl());
        Log::info('FedaPay - Environment: ' . self::getEnvironment());

        try {
            $response = $client->post(self::getBaseUrl() . '/transactions', [
                'json' => $payload,
                'headers' => [
                    'Authorization' => 'Bearer ' . self::getApiKey(),
                    'Content-Type' => 'application/json',
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            Log::info('FedaPay - Transaction created: ' . json_encode($data));

            // Extraire l'ID de la structure FedaPay: {"v1/transaction": {"id": ...}}
            $fedapayTransactionId = $data['v1/transaction']['id'] ?? $data['id'] ?? null;
            $paymentUrl = $data['v1/transaction']['payment_url'] ?? $data['payment_url'] ?? null;

            if (!$fedapayTransactionId) {
                return [
                    'error' => 'ID de transaction non reçu',
                    'code' => 500
                ];
            }

            // Si FedaPay a déjà retourné le payment_url, l'utiliser directement
            if ($paymentUrl) {
                Log::info('FedaPay - Payment URL reçu directement: ' . $paymentUrl);
                return [
                    'data' => [
                        'payment_url' => $paymentUrl,
                        'tx_reference' => $transactionId,
                        'fedapay_transaction_id' => $fedapayTransactionId
                    ],
                    'code' => 0
                ];
            }

            // Sinon, générer le token de paiement via l'endpoint token
            $tokenResponse = $client->post(self::getBaseUrl() . '/transactions/' . $fedapayTransactionId . '/token', [
                'headers' => [
                    'Authorization' => 'Bearer ' . self::getApiKey(),
                    'Content-Type' => 'application/json',
                ]
            ]);

            $tokenData = json_decode($tokenResponse->getBody(), true);
            Log::info('FedaPay - Token generated: ' . json_encode($tokenData));

            return [
                'data' => [
                    'payment_url' => $tokenData['url'] ?? $paymentUrl,
                    'tx_reference' => $transactionId,
                    'token' => $tokenData['token'] ?? null,
                    'fedapay_transaction_id' => $fedapayTransactionId
                ],
                'code' => 0
            ];

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $body = $response ? $response->getBody()->getContents() : 'No response body';
            Log::error('FedaPay - Generate payment link error (ClientException): ' . $e->getMessage());
            Log::error('FedaPay - Response body: ' . $body);
            return [
                'error' => 'Erreur API FedaPay: ' . $e->getMessage() . ' - ' . $body,
                'code' => 500
            ];
        } catch (\Exception $e) {
            Log::error('FedaPay - Generate payment link error: ' . $e->getMessage());
            return [
                'error' => $e->getMessage(),
                'code' => 500
            ];
        }
    }

    /**
     * Vérifie l'état d'une transaction via FedaPay
     */
    public static function checkTransaction($transactionId)
    {
        $client = new Client([
            'timeout' => 30,
            'verify' => false,
        ]);

        try {
            $response = $client->get(self::getBaseUrl() . '/transactions/' . $transactionId, [
                'headers' => [
                    'Authorization' => 'Bearer ' . self::getApiKey(),
                    'Content-Type' => 'application/json',
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            Log::info('FedaPay - Check transaction: ' . json_encode($data));

            // Mapper les statuts FedaPay vers le format CinetPay
            $statusMapping = [
                'approved' => '00',   // Paiement réussi
                'pending' => '01',    // En cours
                'declined' => '02',   // Refusé/Expiré
                'canceled' => '03',   // Annulé
                'transferred' => '00', // Transféré (réussi)
            ];

            $status = $data['status'] ?? 'unknown';
            $cinetpayCode = $statusMapping[$status] ?? '99';

            return [
                'code' => $cinetpayCode,
                'message' => $status === 'approved' ? 'Paiement réussi' : 'Statut: ' . $status,
                'status' => $status,
                'data' => [
                    'amount' => $data['amount'] ?? null,
                    'currency' => $data['currency']['code'] ?? 'XOF',
                    'reference' => $data['reference'] ?? null,
                    'tx_reference' => $data['id'] ?? null,
                    'customer' => $data['customer'] ?? null,
                    'payment_method' => $data['payment_method'] ?? null,
                    'created_at' => $data['created_at'] ?? null,
                    'status' => $status
                ]
            ];

        } catch (\Exception $e) {
            Log::error('FedaPay - Check transaction error: ' . $e->getMessage());
            return [
                'code' => '99',
                'message' => 'Erreur: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Récupère les détails d'un client
     */
    public static function getCustomer($customerId)
    {
        $client = new Client([
            'timeout' => 30,
            'verify' => false,
        ]);

        try {
            $response = $client->get(self::getBaseUrl() . '/customers/' . $customerId, [
                'headers' => [
                    'Authorization' => 'Bearer ' . self::getApiKey(),
                    'Content-Type' => 'application/json',
                ]
            ]);

            return json_decode($response->getBody(), true);

        } catch (\Exception $e) {
            Log::error('FedaPay - Get customer error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Parse le numéro de téléphone pour extraire l'indicatif et le numéro
     * Format FedaPay: number (sans indicatif) + country (code pays)
     * Retourne null si le numéro est invalide
     */
    private static function parsePhoneNumber($phone)
    {
        // Gérer le cas où le téléphone est null ou vide
        if (empty($phone)) {
            return null;
        }

        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // Si après nettoyage le téléphone est vide ou trop court
        if (empty($phone) || strlen($phone) < 8) {
            return null;
        }

        // Mapping des indicatifs vers les codes pays FedaPay
        $countryCodes = [
            '229' => 'bj', // Bénin
            '225' => 'ci', // Côte d'Ivoire
            '221' => 'sn', // Sénégal
            '223' => 'ml', // Mali
            '227' => 'ne', // Niger
            '228' => 'tg', // Togo
            '226' => 'bf', // Burkina Faso
            '241' => 'ga', // Gabon
            '237' => 'cm', // Cameroun
            '242' => 'cg', // Congo
            '235' => 'td', // Tchad
            '240' => 'gq', // Guinée Équatoriale
        ];

        // Retirer le + si présent
        if (strpos($phone, '+') === 0) {
            $phone = substr($phone, 1);
        }

        // Essayer d'extraire l'indicatif
        foreach ($countryCodes as $prefix => $code) {
            if (strpos($phone, (string)$prefix) === 0) {
                // Retirer l'indicatif pour le numéro (FedaPay veut juste le numéro local)
                $numberWithoutPrefix = substr($phone, strlen($prefix));
                return [
                    'number' => $numberWithoutPrefix,
                    'country' => $code
                ];
            }
        }

        // Si pas d'indicatif trouvé, on ne peut pas déterminer le pays
        return null;
    }

    /**
     * Recherche un client par email dans FedaPay
     */
    private static function getCustomerByEmail($email)
    {
        try {
            $client = new Client(['timeout' => 30, 'verify' => false]);

            $response = $client->get(self::getBaseUrl() . '/customers?email=' . urlencode($email), [
                'headers' => [
                    'Authorization' => 'Bearer ' . self::getApiKey(),
                    'Content-Type' => 'application/json',
                ]
            ]);

            $data = json_decode($response->getBody(), true);

            // Vérifier si des customers existent
            if (isset($data['v1/customers']) && count($data['v1/customers']) > 0) {
                return $data['v1/customers'][0]['id'] ?? null;
            }

            return null;
        } catch (\Exception $e) {
            Log::warning('FedaPay - Erreur recherche customer par email: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupère les données complètes d'un client FedaPay par son ID
     */
    private static function getCustomerData($customerId)
    {
        try {
            $client = new Client(['timeout' => 30, 'verify' => false]);

            $response = $client->get(self::getBaseUrl() . '/customers/' . $customerId, [
                'headers' => [
                    'Authorization' => 'Bearer ' . self::getApiKey(),
                    'Content-Type' => 'application/json',
                ]
            ]);

            $data = json_decode($response->getBody(), true);

            // Retourner les données du client
            if (isset($data['v1/customer'])) {
                return [
                    'id' => $data['v1/customer']['id'],
                    'firstname' => $data['v1/customer']['firstname'],
                    'lastname' => $data['v1/customer']['lastname'],
                    'email' => $data['v1/customer']['email']
                ];
            }

            return null;
        } catch (\Exception $e) {
            Log::warning('FedaPay - Erreur récupération données customer: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Vérifie la signature d'un webhook
     */
    public static function verifyWebhookSignature($payload, $signature, $secret)
    {
        try {
            $expectedSignature = hash_hmac('sha256', $payload, $secret);
            return hash_equals($expectedSignature, $signature);
        } catch (\Exception $e) {
            Log::error('FedaPay - Webhook signature verification error: ' . $e->getMessage());
            return false;
        }
    }
}

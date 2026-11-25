<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Vérification SERVEUR d'une transaction Kkiapay.
 * Le widget côté navigateur ne prouve rien : seule cette réponse fait foi.
 * Les clés sont lues via config() uniquement (jamais env()).
 */
class KkiapayService
{
    private const URL_LIVE = 'https://api.kkiapay.me';

    private const URL_SANDBOX = 'https://api-sandbox.kkiapay.me';

    /**
     * @return array{statut: string, montant: int, transaction_id: ?string}
     *
     * @throws RuntimeException si Kkiapay est injoignable, mal configuré ou répond de façon inexploitable
     */
    public function verifierTransaction(string $transactionId): array
    {
        $public = config('services.kkiapay.public_key');
        $private = config('services.kkiapay.private_key');
        $secret = config('services.kkiapay.secret');

        if (! $public || ! $private || ! $secret) {
            throw new RuntimeException('Clés Kkiapay manquantes dans la configuration.');
        }

        $base = config('services.kkiapay.sandbox') ? self::URL_SANDBOX : self::URL_LIVE;

        try {
            $reponse = Http::acceptJson()
                ->asJson()
                ->timeout(15)
                ->withHeaders([
                    'x-api-key' => $public,
                    'x-private-key' => $private,
                    'x-secret-key' => $secret,
                ])
                ->post($base.'/api/v1/transactions/status', ['transactionId' => $transactionId]);
        } catch (ConnectionException $e) {
            throw new RuntimeException('Kkiapay injoignable : '.$e->getMessage(), 0, $e);
        }

        if ($reponse->serverError() || in_array($reponse->status(), [401, 403], true)) {
            // 401/403 : clés refusées (mauvais mode sandbox/live, clés régénérées…)
            throw new RuntimeException('Kkiapay a répondu HTTP '.$reponse->status().'.');
        }

        $donnees = $reponse->json();

        if (! is_array($donnees)) {
            throw new RuntimeException('Réponse Kkiapay illisible (HTTP '.$reponse->status().').');
        }

        return [
            // SUCCESS, FAILED, TRANSACTION_NOT_FOUND… (liste officielle des statuts du SDK)
            'statut' => strtoupper((string) ($donnees['status'] ?? 'INCONNU')),
            'montant' => (int) round((float) ($donnees['amount'] ?? 0)),
            'transaction_id' => isset($donnees['transactionId']) ? (string) $donnees['transactionId'] : null,
        ];
    }
}

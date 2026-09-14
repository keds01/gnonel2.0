<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug FedaPay - Diagnostic complet</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            text-align: center;
        }
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
        }
        .section {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .section h2 {
            color: #444;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .status-ok {
            color: #28a745;
            font-weight: bold;
        }
        .status-error {
            color: #dc3545;
            font-weight: bold;
        }
        .status-warning {
            color: #ffc107;
            font-weight: bold;
        }
        .status-skip {
            color: #6c757d;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
        }
        .error-box {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .success-box {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .json-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            max-height: 300px;
            overflow: auto;
            white-space: pre-wrap;
            word-break: break-all;
        }
        .recommendation {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 0 5px 5px 0;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-success { background: #28a745; color: white; }
        .badge-error { background: #dc3545; color: white; }
        .badge-warning { background: #ffc107; color: black; }
        .badge-info { background: #17a2b8; color: white; }
        .test-result {
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .test-result.ok { background: #d4edda; border-color: #c3e6cb; }
        .test-result.error { background: #f8d7da; border-color: #f5c6cb; }
        .test-result.warning { background: #fff3cd; border-color: #ffeeba; }
        .refresh-btn {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 20px;
        }
        .refresh-btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Debug FedaPay</h1>
        <p class="subtitle">Page de diagnostic complet - {{ now()->format('d/m/Y H:i:s') }}</p>

        {{-- ERREURS CRITIQUES --}}
        @if(count($errors) > 0)
        <div class="section">
            <h2>⚠️ Erreurs Détectées ({{ count($errors) }})</h2>
            @foreach($errors as $error)
            <div class="error-box">
                {{ $error }}
            </div>
            @endforeach
        </div>
        @else
        <div class="section">
            <h2>✅ Aucune Erreur Détectée</h2>
            <div class="success-box">
                Tous les tests de base ont réussi. Si le paiement ne fonctionne toujours pas, le problème est probablement côté FedaPay (URLs non autorisées, configuration compte, etc.)
            </div>
        </div>
        @endif

        {{-- CONFIGURATION --}}
        <div class="section">
            <h2>⚙️ Configuration</h2>
            <table>
                <tr>
                    <th>Paramètre</th>
                    <th>Valeur</th>
                    <th>Statut</th>
                </tr>
                @foreach($debug['config'] as $key => $value)
                <tr>
                    <td>{{ $key }}</td>
                    <td>{{ $value }}</td>
                    <td>
                        @if(strpos($value, 'NON DEFINI') !== false)
                            <span class="badge badge-error">NON DEFINI</span>
                        @elseif($key === 'FEDAPAY_ENVIRONMENT' && $value === 'live')
                            <span class="badge badge-warning">LIVE</span>
                        @elseif($key === 'APP_URL' && (strpos($value, 'localhost') !== false || strpos($value, '127.0.0.1') !== false))
                            <span class="badge badge-warning">LOCALHOST</span>
                        @else
                            <span class="badge badge-success">OK</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </table>
        </div>

        {{-- URLS GÉNÉRÉES --}}
        <div class="section">
            <h2>🔗 URLs Générées</h2>
            <table>
                <tr>
                    <th>Type</th>
                    <th>URL</th>
                    <th>Analyse</th>
                </tr>
                @foreach($debug['urls'] as $type => $url)
                <tr>
                    <td>{{ $type }}</td>
                    <td><code>{{ $url }}</code></td>
                    <td>
                        @if(strpos($url, 'localhost') !== false || strpos($url, '127.0.0.1') !== false)
                            <span class="status-error">❌ URL locale - FedaPay va rejeter</span>
                        @else
                            <span class="status-ok">✅ URL valide</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </table>
            @if(env('FEDAPAY_ENVIRONMENT') === 'live' && (strpos(env('APP_URL'), 'localhost') !== false || strpos(env('APP_URL'), '127.0.0.1') !== false))
            <div class="warning-box" style="margin-top: 15px;">
                <strong>🚨 PROBLÈME CRITIQUE:</strong><br>
                Tu es en mode <b>LIVE</b> avec des URLs <b>localhost</b>.<br>
                FedaPay en production n'accepte PAS les URLs locales.<br>
                Solutions:
                <ul style="margin-top: 10px; margin-left: 20px;">
                    <li>Passe en mode SANDBOX: <code>FEDAPAY_ENVIRONMENT=sandbox</code></li>
                    <li>Ou utilise ngrok pour avoir une URL HTTPS: <code>ngrok http 8000</code></li>
                </ul>
            </div>
            @endif
        </div>

        {{-- TESTS API --}}
        <div class="section">
            <h2>🧪 Tests API</h2>

            {{-- Test Connexion --}}
            @if(isset($tests['api_connection']))
            <div class="test-result {{ $tests['api_connection']['status'] === 'OK' ? 'ok' : 'error' }}">
                <h3>1. Connexion API</h3>
                <p>
                    Statut: <span class="{{ $tests['api_connection']['status'] === 'OK' ? 'status-ok' : 'status-error' }}">
                        {{ $tests['api_connection']['status'] }}
                    </span>
                    @if(isset($tests['api_connection']['code']))
                    | Code HTTP: {{ $tests['api_connection']['code'] }}
                    @endif
                </p>
                @if($tests['api_connection']['status'] !== 'OK')
                <p>Message: {{ $tests['api_connection']['message'] }}</p>
                @endif
            </div>
            @endif

            {{-- Test Création Client --}}
            @if(isset($tests['create_customer']))
            <div class="test-result {{ $tests['create_customer']['status'] === 'OK' ? 'ok' : 'error' }}">
                <h3>2. Création Client</h3>
                <p>
                    Statut: <span class="{{ $tests['create_customer']['status'] === 'OK' ? 'status-ok' : 'status-error' }}">
                        {{ $tests['create_customer']['status'] }}
                    </span>
                    @if(isset($tests['create_customer']['email_test']))
                    | Email test: {{ $tests['create_customer']['email_test'] }}
                    @endif
                </p>
                @if(isset($tests['create_customer']['result']))
                <details>
                    <summary>Voir détails réponse</summary>
                    <div class="json-box">{{ json_encode($tests['create_customer']['result'], JSON_PRETTY_PRINT) }}</div>
                </details>
                @endif
                @if($tests['create_customer']['status'] === 'EXCEPTION' && isset($tests['create_customer']['trace']))
                <details>
                    <summary>Voir stack trace</summary>
                    <div class="json-box">{{ $tests['create_customer']['trace'] }}</div>
                </details>
                @endif
            </div>
            @endif

            {{-- Test Création Transaction --}}
            @if(isset($tests['create_transaction']))
            <div class="test-result
                @if($tests['create_transaction']['status'] === 'OK') ok
                @elseif($tests['create_transaction']['status'] === 'SKIP') warning
                @else error @endif">
                <h3>3. Création Transaction</h3>
                <p>
                    Statut:
                    <span class="
                        @if($tests['create_transaction']['status'] === 'OK') status-ok
                        @elseif($tests['create_transaction']['status'] === 'SKIP') status-warning
                        @else status-error @endif">
                        {{ $tests['create_transaction']['status'] }}
                    </span>
                    @if(isset($tests['create_transaction']['code']))
                    | Code: {{ $tests['create_transaction']['code'] }}
                    @endif
                </p>

                @if(isset($tests['transaction_payload']))
                <details>
                    <summary>Voir payload envoyé</summary>
                    <div class="json-box">{{ json_encode($tests['transaction_payload'], JSON_PRETTY_PRINT) }}</div>
                </details>
                @endif

                @if(isset($tests['create_transaction']['response']))
                <details>
                    <summary>Voir réponse complète</summary>
                    <div class="json-box">{{ json_encode($tests['create_transaction']['response'], JSON_PRETTY_PRINT) }}</div>
                </details>
                @endif

                @if(isset($tests['create_transaction']['response_body']))
                <details open>
                    <summary>⚠️ Corps de la réponse d'erreur</summary>
                    <div class="json-box">{{ $tests['create_transaction']['response_body'] }}</div>
                </details>
                @endif

                @if($tests['create_transaction']['status'] === 'EXCEPTION' && isset($tests['create_transaction']['trace']))
                <details>
                    <summary>Voir stack trace</summary>
                    <div class="json-box">{{ $tests['create_transaction']['trace'] }}</div>
                </details>
                @endif
            </div>
            @endif
        </div>

        {{-- RECOMMANDATIONS --}}
        @if(count($recommendations) > 0)
        <div class="section">
            <h2>💡 Recommandations</h2>
            @foreach($recommendations as $rec)
            <div class="recommendation">
                {{ $rec }}
            </div>
            @endforeach
        </div>
        @endif

        {{-- ACTIONS --}}
        <div class="section" style="text-align: center;">
            <a href="{{ route('debug.fedapay') }}" class="refresh-btn">🔄 Rafraîchir le diagnostic</a>
        </div>

        <div class="section" style="margin-top: 30px; text-align: center; color: #666; font-size: 12px;">
            <p>Debug FedaPay - Généré automatiquement</p>
        </div>
    </div>
</body>
</html>

<?php

/**
 * Script de test pour l'authentification JWT
 * Ce script teste les endpoints d'authentification
 */

// Configuration
$baseUrl = 'http://localhost:8000/api/v1/auth';

// Fonction pour faire des requêtes HTTP
function makeRequest($url, $method = 'GET', $data = null, $token = null)
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    $headers = ['Content-Type: application/json'];
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'code' => $httpCode,
        'body' => json_decode($response, true)
    ];
}

echo "=== Test d'authentification JWT ===\n\n";

// Générer des emails uniques par exécution
$ts = (string) time();
$providerEmail = "test.provider+{$ts}@example.com";
$clientEmail = "test.client+{$ts}@example.com";

// Test 1: Inscription d'un provider
echo "1. Test d'inscription d'un provider...\n";
$providerData = [
    'userType' => 'provider',
    'email' => $providerEmail,
    'password' => 'password123',
    'firstName' => 'John',
    'lastName' => 'Provider'
];

$result = makeRequest($baseUrl . '/register', 'POST', $providerData);
echo "Code: " . $result['code'] . "\n";
echo "Response: " . json_encode($result['body'], JSON_PRETTY_PRINT) . "\n\n";

if ($result['code'] === 201 && isset($result['body']['data']['token'])) {
    $providerToken = $result['body']['data']['token'];
    echo "✅ Inscription provider réussie\n\n";

    // Test 2: Connexion du provider
    echo "2. Test de connexion du provider...\n";
    $loginData = [
        'email' => $providerEmail,
        'password' => 'password123',
        'userType' => 'provider'
    ];

    $result = makeRequest($baseUrl . '/login', 'POST', $loginData);
    echo "Code: " . $result['code'] . "\n";
    echo "Response: " . json_encode($result['body'], JSON_PRETTY_PRINT) . "\n\n";

    if ($result['code'] === 200) {
        echo "✅ Connexion provider réussie\n\n";

        // Extraire le token (peut être dans data.token ou directement dans token)
        $token = $result['body']['data']['token'] ?? $result['body']['token'] ?? null;

        if ($token) {
            // Test 3: Récupération des infos utilisateur
            echo "3. Test de récupération des infos utilisateur...\n";
            $result = makeRequest('http://localhost:8000/api/v1/me', 'GET', null, $token);
            echo "Code: " . $result['code'] . "\n";
            echo "Response: " . json_encode($result['body'], JSON_PRETTY_PRINT) . "\n\n";

            if ($result['code'] === 200) {
                echo "✅ Récupération des infos utilisateur réussie\n\n";
            }
        } else {
            echo "❌ Token non trouvé dans la réponse de connexion\n\n";
        }
    }
}

// Test 4: Inscription d'un client
echo "4. Test d'inscription d'un client...\n";
$clientData = [
    'userType' => 'client',
    'email' => $clientEmail,
    'password' => 'password123',
    'firstName' => 'Jane',
    'lastName' => 'Client'
];

$result = makeRequest($baseUrl . '/register', 'POST', $clientData);
echo "Code: " . $result['code'] . "\n";
echo "Response: " . json_encode($result['body'], JSON_PRETTY_PRINT) . "\n\n";

if ($result['code'] === 201 && isset($result['body']['data']['token'])) {
    echo "✅ Inscription client réussie\n\n";

    // Test 5: Connexion du client
    echo "5. Test de connexion du client...\n";
    $loginData = [
        'email' => $clientEmail,
        'password' => 'password123',
        'userType' => 'client'
    ];

    $result = makeRequest($baseUrl . '/login', 'POST', $loginData);
    echo "Code: " . $result['code'] . "\n";
    echo "Response: " . json_encode($result['body'], JSON_PRETTY_PRINT) . "\n\n";

    if ($result['code'] === 200) {
        echo "✅ Connexion client réussie\n\n";
    }
}

// Test 6: Test de déconnexion
echo "6. Test de déconnexion...\n";
$result = makeRequest($baseUrl . '/logout', 'POST');
echo "Code: " . $result['code'] . "\n";
echo "Response: " . json_encode($result['body'], JSON_PRETTY_PRINT) . "\n\n";

if ($result['code'] === 200) {
    echo "✅ Déconnexion réussie\n\n";
}

echo "=== Fin des tests ===\n";

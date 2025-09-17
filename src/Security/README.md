# Système d'Authentification JWT

Ce document décrit le système d'authentification JWT implémenté pour l'API Symfony.

## Architecture

Le système d'authentification est composé de plusieurs composants :

### 1. AuthService (`src/Service/AuthService.php`)

Service principal qui gère :

- L'extraction et validation des tokens JWT
- L'authentification des utilisateurs
- La vérification des permissions d'accès aux ressources
- La génération de tokens JWT

### 2. AuthSubscriber (`src/EventSubscriber/AuthSubscriber.php`)

EventSubscriber qui intercepte les requêtes et :

- Vérifie si la route est publique ou protégée
- Valide l'authentification pour les routes protégées
- Stocke l'utilisateur dans les attributs de la requête

### 3. JwtAuthenticator (`src/Security/JwtAuthenticator.php`)

Authenticator Symfony qui :

- Valide les tokens JWT depuis l'header Authorization
- Charge les utilisateurs depuis la base de données
- Intègre avec le système de sécurité Symfony

### 4. UserProvider (`src/Security/UserProvider.php`)

Provider qui charge les utilisateurs (Provider/Client) depuis la base de données.

### 5. Handlers d'authentification

- `AuthenticationSuccessHandler` : Gère les connexions réussies
- `AuthenticationFailureHandler` : Gère les échecs d'authentification

## Configuration

### Security.yaml

```yaml
security:
  providers:
    app_user_provider:
      id: App\Security\UserProvider

  firewalls:
    auth:
      pattern: ^/api/v1/auth
      security: false

    public:
      pattern: ^/api/v1/(images|countries|jobs|languages|tags|hard-skills|soft-skills)
      security: false

    api:
      pattern: ^/api/v1
      stateless: true
      provider: app_user_provider
      custom_authenticators:
        - App\Security\JwtAuthenticator

  access_control:
    - { path: ^/api/v1/auth, roles: PUBLIC_ACCESS }
    - {
        path: ^/api/v1/(images|countries|jobs|languages|tags|hard-skills|soft-skills),
        roles: PUBLIC_ACCESS,
      }
    - {
        path: ^/api/v1/(providers|clients|services|articles|reviews|bookings|requests),
        methods: [GET],
        roles: IS_AUTHENTICATED,
      }
    - {
        path: ^/api/v1/(providers|clients|services|articles|reviews|bookings|requests),
        methods: [POST, PUT, DELETE],
        roles: IS_AUTHENTICATED,
      }
```

### CORS

Configuration CORS avec NelmioCorsBundle pour autoriser les requêtes cross-origin.

## Utilisation

### 1. Connexion

```bash
POST /api/v1/auth/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password123",
    "userType": "provider"
}
```

Réponse :

```json
{
  "success": true,
  "message": "Connexion réussie",
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "user": {
      "id": 1,
      "email": "user@example.com",
      "firstName": "John",
      "lastName": "Doe",
      "type": "provider"
    }
  }
}
```

### 2. Utilisation du token

```bash
GET /api/v1/providers/1
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

### 3. Dans un contrôleur

```php
use App\Service\AuthService;

class ExampleController extends AbstractController
{
    public function __construct(
        private AuthService $authService
    ) {}

    public function protectedAction(): JsonResponse
    {
        if (!$this->authService->isAuthenticated()) {
            return new JsonResponse(['error' => 'Non authentifié'], 401);
        }

        $user = $this->authService->getCurrentUser();
        $userId = $this->authService->getCurrentUserId();

        // Vérifier l'accès à une ressource
        if (!$this->authService->canAccessResource('provider', $userId)) {
            return new JsonResponse(['error' => 'Accès refusé'], 403);
        }

        return new JsonResponse(['data' => $user]);
    }
}
```

## Routes publiques vs protégées

### Routes publiques (pas d'authentification requise)

- `/api/v1/auth/*` - Authentification
- `/api/v1/images/*` - Images statiques
- `/api/v1/countries` - Liste des pays
- `/api/v1/jobs` - Liste des métiers
- `/api/v1/languages` - Liste des langues
- `/api/v1/tags` - Liste des tags
- `/api/v1/hard-skills` - Liste des compétences techniques
- `/api/v1/soft-skills` - Liste des compétences relationnelles

### Routes protégées (authentification requise)

- `/api/v1/providers/*` - Gestion des providers
- `/api/v1/clients/*` - Gestion des clients
- `/api/v1/services/*` - Gestion des services
- `/api/v1/articles/*` - Gestion des articles
- `/api/v1/reviews/*` - Gestion des avis
- `/api/v1/bookings/*` - Gestion des réservations
- `/api/v1/requests/*` - Gestion des demandes

## Permissions d'accès

Le système vérifie automatiquement les permissions selon le type d'utilisateur :

### Provider

- Peut accéder à ses propres données
- Peut gérer ses services et articles
- Peut voir les demandes qui lui sont adressées
- Peut voir les avis qui lui sont donnés

### Client

- Peut accéder à ses propres données
- Peut voir tous les services et articles publics
- Peut gérer ses propres réservations et demandes
- Peut créer des avis

## Sécurité

### Tokens JWT

- **Format** : `Bearer <token>`
- **Durée** : 1 heure (configurable)
- **Validation** : Signature, expiration, structure

### Headers requis

```
Authorization: Bearer <token>
Content-Type: application/json
```

### Gestion des erreurs

```json
{
  "success": false,
  "error": "Token invalide ou expiré",
  "code": "UNAUTHORIZED"
}
```

## TODO - Améliorations futures

1. **Implémentation JWT réelle** : Remplacer la simulation par une vraie bibliothèque JWT
2. **Hashage des mots de passe** : Implémenter le hashage avec UserPasswordHasherInterface
3. **Refresh tokens** : Ajouter un système de refresh tokens
4. **Rate limiting** : Limiter le nombre de tentatives de connexion
5. **Audit logs** : Logger les tentatives de connexion et accès
6. **Rôles avancés** : Implémenter un système de rôles plus granulaire
7. **2FA** : Ajouter l'authentification à deux facteurs
8. **OAuth2** : Support pour OAuth2/OpenID Connect

## Tests

Utiliser `src/Service/Example/AuthenticationExample.php` pour tester les fonctionnalités :

```php
use App\Service\Example\AuthenticationExample;

$example = new AuthenticationExample($authService);
$result = $example->exampleControllerUsage();
```

# Guide d'authentification JWT

## Vue d'ensemble

L'API utilise l'authentification JWT (JSON Web Token) avec Lexik JWT Authentication Bundle pour gérer les connexions des providers et clients.

## Configuration

### Variables d'environnement requises

Assurez-vous que ces variables sont définies dans votre fichier `.env` :

```env
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=your_passphrase_here
```

### Génération des clés JWT

Si vous n'avez pas encore généré les clés JWT, exécutez :

```bash
php bin/console lexik:jwt:generate-keypair
```

## Endpoints d'authentification

### 1. Inscription

**POST** `/api/v1/auth/register`

Inscrit un nouveau provider ou client.

#### Payload

```json
{
  "userType": "provider", // ou "client"
  "email": "user@example.com",
  "password": "motdepasse123",
  "firstName": "John",
  "lastName": "Doe"
}
```

#### Réponse

```json
{
  "success": true,
  "message": "Inscription réussie",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
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

### 2. Connexion

**POST** `/api/v1/auth/login`

Connecte un utilisateur existant.

#### Payload

```json
{
  "email": "user@example.com",
  "password": "motdepasse123",
  "userType": "provider" // ou "client"
}
```

#### Réponse

```json
{
  "success": true,
  "message": "Connexion réussie",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
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

### 3. Informations utilisateur

**GET** `/api/v1/auth/me`

Récupère les informations de l'utilisateur connecté.

#### Headers requis

```
Authorization: Bearer <token>
```

#### Réponse (Provider)

```json
{
  "success": true,
  "data": {
    "id": 1,
    "firstName": "John",
    "lastName": "Doe",
    "email": "user@example.com",
    "profilePicture": null,
    "joinedAt": "2024-01-15T10:30:00+00:00",
    "slug": "john-doe-abc123",
    "job": "Développeur Web",
    "country": "France",
    "city": "Paris",
    "state": "Île-de-France",
    "postalCode": "75001",
    "address": "123 Rue Example",
    "hardSkills": ["PHP", "Symfony", "JavaScript"],
    "softSkills": ["Communication", "Leadership"],
    "languages": ["Français", "Anglais"],
    "role": "provider"
  }
}
```

#### Réponse (Client)

```json
{
  "success": true,
  "data": {
    "id": 1,
    "firstName": "Jane",
    "lastName": "Client",
    "email": "client@example.com",
    "profilePicture": null,
    "joinedAt": "2024-01-15T10:30:00+00:00",
    "slug": "jane-client-def456",
    "country": "France",
    "city": "Lyon",
    "state": "Auvergne-Rhône-Alpes",
    "postalCode": "69001",
    "address": "456 Avenue Example",
    "role": "client"
  }
}
```

### 4. Déconnexion

**POST** `/api/v1/auth/logout`

Déconnecte l'utilisateur actuel.

#### Réponse

```json
{
  "success": true,
  "message": "Déconnexion réussie"
}
```

## Utilisation des tokens

### Méthode 1: Header Authorization

```bash
curl -H "Authorization: Bearer <token>" \
     http://localhost:8000/api/v1/auth/me
```

### Méthode 2: Cookie (pour les applications web)

Le token est automatiquement stocké dans un cookie `authToken` lors de la connexion.

## Sécurité

### Configuration de sécurité

Le fichier `config/packages/security.yaml` configure :

- **Firewalls** : Routes publiques vs protégées
- **Access Control** : Permissions par route et méthode HTTP
- **User Provider** : Chargement des utilisateurs (Provider/Client)
- **JWT Authenticator** : Validation des tokens

### Routes publiques

- `/api/v1/auth/*` - Authentification
- `/api/v1/images/*` - Images
- `/api/v1/countries` - Liste des pays
- `/api/v1/jobs` - Liste des métiers
- `/api/v1/languages` - Liste des langues
- `/api/v1/tags` - Liste des tags
- `/api/v1/hard-skills` - Liste des compétences techniques
- `/api/v1/soft-skills` - Liste des compétences comportementales
- `GET /api/v1/providers` - Liste des providers
- `GET /api/v1/clients` - Liste des clients
- `GET /api/v1/services` - Liste des services
- `GET /api/v1/articles` - Liste des articles

### Routes protégées

Toutes les autres routes nécessitent une authentification JWT valide.

## Test de l'authentification

Un script de test est fourni dans `test_auth.php` :

```bash
php test_auth.php
```

Ce script teste :

1. Inscription d'un provider
2. Connexion du provider
3. Récupération des infos utilisateur
4. Inscription d'un client
5. Connexion du client
6. Déconnexion

## Gestion des erreurs

### Erreurs courantes

- **401 Unauthorized** : Token manquant, invalide ou expiré
- **400 Bad Request** : Données de connexion/inscription invalides
- **409 Conflict** : Email déjà utilisé lors de l'inscription

### Format des erreurs

```json
{
  "success": false,
  "error": "Message d'erreur détaillé",
  "code": "ERROR_CODE"
}
```

## Développement

### Structure des fichiers

- `src/Controller/AuthController.php` - Contrôleur d'authentification
- `src/Security/UserProvider.php` - Provider d'utilisateurs
- `src/Security/JwtAuthenticator.php` - Authentificateur JWT
- `src/Service/AuthService.php` - Service d'authentification
- `src/Entity/Provider.php` - Entité Provider
- `src/Entity/Client.php` - Entité Client

### Améliorations futures

- [ ] Validation avancée des mots de passe
- [ ] Réinitialisation de mot de passe
- [ ] Refresh tokens
- [ ] Rate limiting
- [ ] Audit des connexions


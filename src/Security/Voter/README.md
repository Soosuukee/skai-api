# Voters - Gestion des Permissions

## 📋 Vue d'ensemble

Les Voters permettent de gérer les permissions de manière granulaire et centralisée. Ils décident si un utilisateur peut effectuer une action sur une ressource.

## 🔐 ArticleVoter

### Permissions disponibles

| Permission | Description         | Logique                                         |
| ---------- | ------------------- | ----------------------------------------------- |
| `VIEW`     | Voir l'article      | Article publié (public) OU utilisateur = auteur |
| `EDIT`     | Modifier l'article  | Utilisateur connecté = auteur                   |
| `DELETE`   | Supprimer l'article | Utilisateur connecté = auteur                   |
| `PUBLISH`  | Publier l'article   | Utilisateur connecté = auteur                   |

### Utilisation dans un Controller

```php
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ArticleController extends AbstractController
{
    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker
    ) {}

    public function edit(Article $article): Response
    {
        // Vérifier si l'utilisateur peut modifier cet article
        if (!$this->authorizationChecker->isGranted('EDIT', $article)) {
            throw new AccessDeniedException('Vous ne pouvez pas modifier cet article');
        }

        // Logique d'édition...
    }
}
```

### Utilisation dans un Template

```twig
{# Vérifier les permissions #}
{% if is_granted('EDIT', article) %}
    <a href="{{ path('article_edit', {id: article.id}) }}">Modifier</a>
{% endif %}

{% if is_granted('DELETE', article) %}
    <a href="{{ path('article_delete', {id: article.id}) }}">Supprimer</a>
{% endif %}
```

### Utilisation avec API Platform

```php
#[ApiResource(
    operations: [
        new Get(),
        new Post(security: "is_granted('EDIT', object)"),
        new Put(security: "is_granted('EDIT', object)"),
        new Delete(security: "is_granted('DELETE', object)"),
    ]
)]
class Article
{
    // ...
}
```

## 🧪 Test des Permissions

### Endpoint de test

```bash
# Vérifier les permissions d'un article
GET /api/v1/articles/{id}/permissions
```

### Réponse exemple

```json
{
  "article_id": 1,
  "article_title": "Mon Article",
  "permissions": {
    "can_view": true,
    "can_edit": true,
    "can_delete": true,
    "can_publish": false
  }
}
```

## 🔧 Configuration

Le Voter est automatiquement enregistré par Symfony. Aucune configuration supplémentaire n'est nécessaire.

## 📝 Logique des Permissions

### VIEW

- ✅ Article publié → **Tous peuvent voir (même non connectés)**
- ✅ Article non publié + Utilisateur connecté = auteur → Peut voir
- ❌ Article non publié + Utilisateur non connecté → Ne peut pas voir
- ❌ Article non publié + Utilisateur connecté ≠ auteur → Ne peut pas voir

### EDIT / DELETE / PUBLISH

- ✅ Utilisateur connecté = auteur → Peut faire l'action
- ❌ Utilisateur non connecté → Ne peut pas faire l'action
- ❌ Utilisateur connecté ≠ auteur → Ne peut pas faire l'action

## 🔐 ServiceVoter

### Permissions disponibles

| Permission | Description          | Logique                                              |
| ---------- | -------------------- | ---------------------------------------------------- |
| `VIEW`     | Voir le service      | Service actif (public) OU utilisateur = propriétaire |
| `EDIT`     | Modifier le service  | Utilisateur connecté = propriétaire                  |
| `DELETE`   | Supprimer le service | Utilisateur connecté = propriétaire                  |
| `BOOK`     | Réserver le service  | Service actif ET utilisateur ≠ propriétaire          |
| `ACTIVATE` | Activer le service   | Utilisateur connecté = propriétaire                  |

### Utilisation dans un Controller

```php
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ServiceController extends AbstractController
{
    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker
    ) {}

    public function book(Service $service): Response
    {
        if (!$this->authorizationChecker->isGranted('BOOK', $service)) {
            throw new AccessDeniedException('Vous ne pouvez pas réserver ce service');
        }

        // Logique de réservation...
    }
}
```

### Test des Permissions

```bash
# Vérifier les permissions d'un service
GET /api/v1/services/{id}/permissions
```

### Réponse exemple

```json
{
  "service_id": 1,
  "service_title": "Mon Service",
  "is_active": true,
  "permissions": {
    "can_view": true,
    "can_edit": true,
    "can_delete": true,
    "can_book": false,
    "can_activate": true
  }
}
```

## 🔐 ProviderVoter

### Permissions disponibles

| Permission        | Description               | Logique                             |
| ----------------- | ------------------------- | ----------------------------------- |
| `VIEW`            | Voir le profil            | Toujours autorisé (profils publics) |
| `EDIT`            | Modifier le profil        | Utilisateur connecté = propriétaire |
| `DELETE`          | Supprimer le compte       | Utilisateur connecté = propriétaire |
| `VIEW_CONTACT`    | Voir les infos de contact | Utilisateur connecté = propriétaire |
| `VIEW_STATS`      | Voir les statistiques     | Utilisateur connecté = propriétaire |
| `MANAGE_SERVICES` | Gérer les services        | Utilisateur connecté = propriétaire |
| `MANAGE_ARTICLES` | Gérer les articles        | Utilisateur connecté = propriétaire |

### Utilisation dans un Controller

```php
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ProviderController extends AbstractController
{
    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker
    ) {}

    public function editProfile(Provider $provider): Response
    {
        if (!$this->authorizationChecker->isGranted('EDIT', $provider)) {
            throw new AccessDeniedException('Vous ne pouvez pas modifier ce profil');
        }

        // Logique d'édition...
    }
}
```

### Test des Permissions

```bash
# Vérifier les permissions d'un provider
GET /api/v1/providers/{id}/permissions
```

### Réponse exemple

```json
{
  "provider_id": 1,
  "provider_name": "John Doe",
  "provider_email": "john@example.com",
  "permissions": {
    "can_view": true,
    "can_edit": true,
    "can_delete": true,
    "can_view_contact": true,
    "can_view_stats": true,
    "can_manage_services": true,
    "can_manage_articles": true
  }
}
```

## 🔐 ClientVoter

### Permissions disponibles

| Permission        | Description               | Logique                             |
| ----------------- | ------------------------- | ----------------------------------- |
| `VIEW`            | Voir le profil            | Toujours autorisé (profils publics) |
| `EDIT`            | Modifier le profil        | Utilisateur connecté = propriétaire |
| `DELETE`          | Supprimer le compte       | Utilisateur connecté = propriétaire |
| `VIEW_CONTACT`    | Voir les infos de contact | Utilisateur connecté = propriétaire |
| `VIEW_STATS`      | Voir les statistiques     | Utilisateur connecté = propriétaire |
| `MANAGE_BOOKINGS` | Gérer les réservations    | Utilisateur connecté = propriétaire |
| `MANAGE_REVIEWS`  | Gérer les avis            | Utilisateur connecté = propriétaire |

### Utilisation dans un Controller

```php
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ClientController extends AbstractController
{
    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker
    ) {}

    public function editProfile(Client $client): Response
    {
        if (!$this->authorizationChecker->isGranted('EDIT', $client)) {
            throw new AccessDeniedException('Vous ne pouvez pas modifier ce profil');
        }

        // Logique d'édition...
    }
}
```

### Test des Permissions

```bash
# Vérifier les permissions d'un client
GET /api/v1/clients/{id}/permissions
```

### Réponse exemple

```json
{
  "client_id": 1,
  "client_name": "Jane Doe",
  "client_email": "jane@example.com",
  "permissions": {
    "can_view": true,
    "can_edit": true,
    "can_delete": true,
    "can_view_contact": true,
    "can_view_stats": true,
    "can_manage_bookings": true,
    "can_manage_reviews": true
  }
}
```

## 🚀 Prochaines étapes

1. Ajouter des tests unitaires
2. Intégrer avec les templates frontend
3. Optimiser les autres services

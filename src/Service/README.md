# Services de Slugification

Ce dossier contient tous les services nécessaires pour la génération automatique de slugs pour les entités de l'application.

## Services disponibles

### 1. SlugService

Service principal de slugification qui fournit les méthodes de base :

- `slugify(string $text): string` - Convertit un texte en slug
- `generateUniqueSlug(string $baseSlug, callable $checkSlugExists): string` - Génère un slug unique
- `slugifyFullName(string $firstName, string $lastName, int $id): string` - Slug pour nom complet avec ID
- `slugifyTitle(string $title): string` - Slug pour titre

### 2. Services spécialisés

#### ProviderSlugService

Génère des slugs pour les providers au format : `firstname-lastname-{shortId}`

- Exemple : `jean-dupont-a1b2c3d4`

#### ClientSlugService

Génère des slugs pour les clients au format : `firstname-lastname-{shortId}`

- Exemple : `marie-martin-e5f6g7h8`

#### ArticleSlugService

Génère des slugs pour les articles basés sur le titre :

- Exemple : `comment-creer-une-api-rest-avec-symfony`

#### ServiceSlugService

Génère des slugs pour les services basés sur le titre :

- Exemple : `developpement-dapplication-web`

### 3. SlugManager

Service centralisé qui orchestre tous les services de slugification.

### 4. Event Listeners

#### SlugEventListener

Écoute les événements Doctrine pour générer automatiquement les slugs :

- `prePersist` : Génère un slug lors de la création
- `preUpdate` : Met à jour le slug lors de la modification

#### SlugPostPersistListener

Met à jour les slugs des providers et clients avec leur ID réel après la sauvegarde.

## Utilisation

### Utilisation automatique (recommandée)

Les slugs sont générés automatiquement grâce aux event listeners :

```php
// Créer un provider
$provider = new Provider();
$provider->setFirstName('Jean');
$provider->setLastName('Dupont');
$provider->setEmail('jean.dupont@example.com');

// Le slug sera généré automatiquement lors de la sauvegarde
$entityManager->persist($provider);
$entityManager->flush();

// Le slug final sera : "jean-dupont-a1b2c3d4" (avec l'ID court)
```

### Utilisation manuelle

```php
use App\Service\SlugManager;

class MyService
{
    public function __construct(
        private SlugManager $slugManager
    ) {}

    public function createProvider(string $firstName, string $lastName): Provider
    {
        $provider = new Provider();
        $provider->setFirstName($firstName);
        $provider->setLastName($lastName);

        // Générer un slug temporaire
        $slug = $this->slugManager->generateProviderSlug($firstName, $lastName);
        $provider->setSlug($slug);

        return $provider;
    }
}
```

## Formats de slugs

### Providers et Clients

Format : `{prénom}-{nom}-{id-court}`

- Le prénom et nom sont convertis en minuscules et sans accents
- L'ID court est l'ID numérique converti en base 36
- Exemples :
  - `jean-dupont-a1b2c3d4`
  - `marie-martin-e5f6g7h8`

### Articles et Services

Format : `{titre-sluggifié}`

- Le titre est converti en minuscules, sans accents, avec des tirets
- Exemples :
  - `comment-creer-une-api-rest-avec-symfony`
  - `developpement-dapplication-web`

## Gestion des doublons

Tous les services gèrent automatiquement les doublons en ajoutant un suffixe numérique :

- `mon-article`
- `mon-article-1`
- `mon-article-2`
- etc.

## Configuration

Les services sont automatiquement enregistrés dans le conteneur de dépendances Symfony grâce à l'autoconfiguration dans `config/services.yaml`.

## Tests

Voir `src/Service/Example/SlugUsageExample.php` pour des exemples d'utilisation complets.

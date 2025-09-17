# Contrôleurs API

Ce dossier contient tous les contrôleurs de l'API REST de l'application.

## Contrôleurs créés

### **ProviderController** (`/api/v1/providers`)

- `GET /api/v1/providers` - Lister tous les providers
- `GET /api/v1/providers/{id}` - Récupérer un provider par ID
- `GET /api/v1/providers/slug/{slug}` - Récupérer un provider par slug
- `POST /api/v1/providers` - Créer un nouveau provider
- `PUT /api/v1/providers/{id}` - Modifier un provider
- `DELETE /api/v1/providers/{id}` - Supprimer un provider
- `POST /api/v1/providers/{id}/profile-image` - Upload image de profil
- `GET /api/v1/providers/{id}/services` - Services d'un provider
- `GET /api/v1/providers/{id}/articles` - Articles d'un provider
- `GET /api/v1/providers/{id}/reviews` - Avis d'un provider

### **ClientController** (`/api/v1/clients`)

- `GET /api/v1/clients` - Lister tous les clients
- `GET /api/v1/clients/{id}` - Récupérer un client par ID
- `GET /api/v1/clients/slug/{slug}` - Récupérer un client par slug
- `POST /api/v1/clients` - Créer un nouveau client
- `PUT /api/v1/clients/{id}` - Modifier un client
- `DELETE /api/v1/clients/{id}` - Supprimer un client
- `POST /api/v1/clients/{id}/profile-image` - Upload image de profil

### **ServiceController** (`/api/v1/services`)

- `GET /api/v1/services` - Lister tous les services
- `GET /api/v1/services/{id}` - Récupérer un service par ID
- `GET /api/v1/services/slug/{slug}` - Récupérer un service par slug
- `POST /api/v1/services` - Créer un nouveau service
- `PUT /api/v1/services/{id}` - Modifier un service
- `DELETE /api/v1/services/{id}` - Supprimer un service
- `POST /api/v1/services/{id}/images` - Upload images de service

### **ArticleController** (`/api/v1/articles`)

- `GET /api/v1/articles` - Lister tous les articles
- `GET /api/v1/articles/{id}` - Récupérer un article par ID
- `GET /api/v1/articles/slug/{slug}` - Récupérer un article par slug
- `POST /api/v1/articles` - Créer un nouvel article
- `PUT /api/v1/articles/{id}` - Modifier un article
- `DELETE /api/v1/articles/{id}` - Supprimer un article
- `POST /api/v1/articles/{id}/images` - Upload images d'article

### **ReviewController** (`/api/v1/reviews`)

- `GET /api/v1/reviews` - Lister tous les avis
- `GET /api/v1/reviews/{id}` - Récupérer un avis par ID
- `POST /api/v1/reviews` - Créer un nouvel avis
- `PUT /api/v1/reviews/{id}` - Modifier un avis
- `DELETE /api/v1/reviews/{id}` - Supprimer un avis

### **BookingController** (`/api/v1/bookings`)

- `GET /api/v1/bookings` - Lister toutes les réservations
- `GET /api/v1/bookings/{id}` - Récupérer une réservation par ID
- `POST /api/v1/bookings` - Créer une nouvelle réservation
- `PUT /api/v1/bookings/{id}` - Modifier une réservation
- `DELETE /api/v1/bookings/{id}` - Supprimer une réservation

### **RequestController** (`/api/v1/requests`)

- `GET /api/v1/requests` - Lister toutes les demandes
- `GET /api/v1/requests/{id}` - Récupérer une demande par ID
- `POST /api/v1/requests` - Créer une nouvelle demande
- `PUT /api/v1/requests/{id}` - Modifier une demande
- `DELETE /api/v1/requests/{id}` - Supprimer une demande

### **AuthController** (`/api/v1/auth`)

- `POST /api/v1/auth/login` - Connexion
- `POST /api/v1/auth/register` - Inscription
- `POST /api/v1/auth/logout` - Déconnexion
- `GET /api/v1/auth/me` - Profil utilisateur connecté

### **Contrôleurs de ressources**

#### **CountryController** (`/api/v1/countries`)

- `GET /api/v1/countries` - Lister tous les pays
- `GET /api/v1/countries/{id}` - Récupérer un pays par ID

#### **JobController** (`/api/v1/jobs`)

- `GET /api/v1/jobs` - Lister tous les métiers
- `GET /api/v1/jobs/{id}` - Récupérer un métier par ID
- `GET /api/v1/jobs/slug/{slug}` - Récupérer un métier par slug

#### **LanguageController** (`/api/v1/languages`)

- `GET /api/v1/languages` - Lister toutes les langues
- `GET /api/v1/languages/{id}` - Récupérer une langue par ID

#### **TagController** (`/api/v1/tags`)

- `GET /api/v1/tags` - Lister tous les tags
- `GET /api/v1/tags/{id}` - Récupérer un tag par ID
- `GET /api/v1/tags/slug/{slug}` - Récupérer un tag par slug

#### **HardSkillController** (`/api/v1/hard-skills`)

- `GET /api/v1/hard-skills` - Lister toutes les compétences techniques
- `GET /api/v1/hard-skills/{id}` - Récupérer une compétence technique par ID

#### **SoftSkillController** (`/api/v1/soft-skills`)

- `GET /api/v1/soft-skills` - Lister toutes les compétences comportementales
- `GET /api/v1/soft-skills/{id}` - Récupérer une compétence comportementale par ID

## Fonctionnalités communes

### **Réponses JSON standardisées**

Tous les contrôleurs retournent des réponses JSON avec le format suivant :

```json
{
    "success": true,
    "data": {...},
    "message": "Operation successful",
    "total": 100
}
```

### **Gestion d'erreurs**

```json
{
  "success": false,
  "error": "Error message",
  "errors": "Validation errors"
}
```

### **Codes de statut HTTP**

- `200` - Succès
- `201` - Création réussie
- `400` - Erreur de validation
- `404` - Ressource non trouvée
- `500` - Erreur serveur

### **Upload de fichiers**

Les contrôleurs supportent l'upload de fichiers avec :

- Validation des types de fichiers
- Gestion des erreurs d'upload
- Organisation hiérarchique des fichiers

### **Validation**

Tous les contrôleurs utilisent le composant Validator de Symfony pour :

- Valider les données d'entrée
- Retourner des erreurs détaillées
- Assurer l'intégrité des données

## Services utilisés

- **FileUploadService** - Gestion des uploads de fichiers
- **SlugManager** - Génération de slugs
- **Repositories** - Accès aux données
- **EntityManager** - Gestion des entités
- **Validator** - Validation des données
- **Serializer** - Sérialisation des réponses

## TODO

- [ ] Implémenter l'authentification JWT
- [ ] Ajouter la validation des relations entre entités
- [ ] Implémenter la pagination pour les listes
- [ ] Ajouter les filtres et la recherche
- [ ] Implémenter la gestion des permissions
- [ ] Ajouter la documentation OpenAPI/Swagger

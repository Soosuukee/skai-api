# Service d'Upload de Fichiers

Ce service gère l'upload sécurisé de fichiers pour l'application, avec validation et organisation automatique.

## Structure des dossiers

```
public/uploads/
├── profiles/
│   ├── providers/     # Photos de profil des providers
│   └── clients/       # Photos de profil des clients
├── services/
│   ├── covers/        # Images de couverture des services
│   └── images/        # Images de contenu des services
├── articles/
│   ├── covers/        # Images de couverture des articles
│   └── images/        # Images de contenu des articles
├── experiences/       # Logos d'entreprises (expériences)
├── educations/        # Logos d'institutions (formations)
└── completed-works/   # Médias des travaux réalisés
```

## Types de fichiers autorisés

### Images

- **Extensions** : jpg, jpeg, png, gif, webp
- **Types MIME** : image/jpeg, image/png, image/gif, image/webp
- **Taille max** : 5MB

### Documents

- **Extensions** : pdf, doc, docx, txt
- **Types MIME** : application/pdf, application/msword, application/vnd.openxmlformats-officedocument.wordprocessingml.document, text/plain
- **Taille max** : 10MB

## Méthodes disponibles

### Upload de fichiers

```php
// Photo de profil de provider
$fileUrl = $fileUploadService->uploadProviderProfilePicture($file, $providerId);

// Photo de profil de client
$fileUrl = $fileUploadService->uploadClientProfilePicture($file, $clientId);

// Image de couverture de service
$fileUrl = $fileUploadService->uploadServiceCover($file, $serviceId);

// Image de contenu de service
$fileUrl = $fileUploadService->uploadServiceImage($file, $serviceId);

// Image de couverture d'article
$fileUrl = $fileUploadService->uploadArticleCover($file, $articleId);

// Image de contenu d'article
$fileUrl = $fileUploadService->uploadArticleImage($file, $articleId);

// Logo d'entreprise (expérience)
$fileUrl = $fileUploadService->uploadExperienceLogo($file, $experienceId);

// Logo d'institution (formation)
$fileUrl = $fileUploadService->uploadEducationLogo($file, $educationId);

// Média de travail réalisé
$fileUrl = $fileUploadService->uploadCompletedWorkMedia($file, $workId);
```

### Gestion des fichiers

```php
// Supprimer un fichier
$deleted = $fileUploadService->deleteFile($fileUrl);

// Vérifier l'existence d'un fichier
$exists = $fileUploadService->fileExists($fileUrl);

// Obtenir les informations d'un fichier
$fileInfo = $fileUploadService->getFileInfo($fileUrl);

// Upload multiple de fichiers
$uploadedFiles = $fileUploadService->uploadMultipleFiles($files, $directory, $prefix);

// Nettoyer les fichiers orphelins
$deletedCount = $fileUploadService->cleanupOrphanedFiles($directory, 30);
```

## Exemple d'utilisation complète

```php
use App\Service\FileUploadService;

class ProfileController
{
    public function __construct(
        private FileUploadService $fileUploadService
    ) {}

    public function uploadProfilePicture(Request $request, int $providerId): JsonResponse
    {
        $uploadedFile = $request->files->get('profile_picture');

        if (!$uploadedFile) {
            return new JsonResponse(['error' => 'Aucun fichier fourni'], 400);
        }

        try {
            $fileUrl = $this->fileUploadService->uploadProviderProfilePicture(
                $uploadedFile->getPathname(),
                $providerId
            );

            return new JsonResponse([
                'success' => true,
                'file_url' => $fileUrl,
                'message' => 'Photo de profil uploadée avec succès'
            ]);
        } catch (\RuntimeException $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
```

## Validation de sécurité

Le service effectue plusieurs validations :

1. **Vérification des erreurs d'upload** - Codes d'erreur PHP
2. **Vérification HTTP POST** - Fichier uploadé via formulaire
3. **Extension de fichier** - Extensions autorisées uniquement
4. **Type MIME** - Vérification du type réel du fichier
5. **Taille du fichier** - Respect des limites définies
6. **Nom de fichier** - Nettoyage et sécurisation

## Gestion des erreurs

Les erreurs courantes et leurs messages :

- `UPLOAD_ERR_INI_SIZE` : "Le fichier dépasse la taille maximale autorisée par le serveur"
- `UPLOAD_ERR_FORM_SIZE` : "Le fichier dépasse la taille maximale autorisée par le formulaire"
- `UPLOAD_ERR_PARTIAL` : "Le fichier n'a été que partiellement uploadé"
- `UPLOAD_ERR_NO_FILE` : "Aucun fichier n'a été uploadé"
- Extension non autorisée : "Extension non autorisée. Extensions autorisées: jpg, jpeg, png, gif, webp"
- Type MIME non autorisé : "Type de fichier non autorisé: application/x-executable"
- Fichier trop volumineux : "Fichier trop volumineux. Taille actuelle: 10MB, Taille max: 5MB"

## Configuration

Ajoutez ces variables dans votre fichier `.env` :

```env
UPLOAD_PATH=public/uploads/
APP_URL=http://localhost:8000
```

## Noms de fichiers générés

Les fichiers sont renommés automatiquement avec un nom unique :

- Format : `{prefix}_{timestamp_unique}.{extension}`
- Exemple : `provider-123_64f8a2b1c3d4e.jpg`

Cela garantit l'unicité et évite les conflits de noms.

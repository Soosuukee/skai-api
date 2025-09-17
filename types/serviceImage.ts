export interface ServiceImage {
  id: number;                       // Identifiant unique de l'image
  serviceContentId: number;         // Référence au service
  url: string;               // URL de l'image
}

// Interface étendue pour l'affichage avec des propriétés calculées
export interface ServiceImageWithMetadata extends ServiceImage {
  alt?: string;              // Texte alternatif pour l'accessibilité
  width?: number;            // Largeur de l'image
  height?: number;           // Hauteur de l'image
  description?: string;      // Description optionnelle de l'image
}

// Interface pour les options de filtrage des images
export interface ServiceImageFilters {
  serviceContentId?: number;
}

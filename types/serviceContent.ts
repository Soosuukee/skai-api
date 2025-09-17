import { ServiceImage } from "./serviceImage";

export interface ServiceContent {
  id: number; // Identifiant unique du contenu
  serviceSectionId: number; // Référence au service
  content: string; // Titre du contenu
  images?: ServiceImage[];
}

// Interface pour les options de filtrage des contenus
export interface ServiceContentFilters {
  serviceSectionId?: number;
}

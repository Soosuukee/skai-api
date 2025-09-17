import { ServiceContent } from "./serviceContent";

export interface ServiceSection {
  id: number; // Identifiant unique de la section
  serviceId: number; // Référence au service
  title: string; // Titre de la section
  content?: ServiceContent[];
}

// Interface pour les options de filtrage des sections
export interface ServiceSectionFilters {
  serviceId?: number;
}

import { ServiceSection } from "./serviceSection";
import { Tag } from "./tag";

export interface Service {
  id: number; // Identifiant unique du service
  providerId: number; // Référence au prestataire
  createdAt: string
  maxPrice?: number | null;
  minPrice?: number | null;
  isActive: boolean;
  isFeatured: boolean;
  cover?: string;
  title: string;
  summary: string;
  slug: string;
  sections?: ServiceSection[];
  tags?: Tag[];
}

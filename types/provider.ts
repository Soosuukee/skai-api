import { HardSkill } from './hardskill';
import { SoftSkill } from './softskill';
import { Job } from './job';
import { SocialLink } from './socialLink';
import { Service } from './service';
import { Article } from './article';
import { Experience } from './experience';
import { Education } from './education';
import { Language } from './language';
import { Country } from './country';

export interface Provider {
  id: number;
  firstName: string;
  lastName: string;
  email: string;
  profilePicture: string | null;
  joinedAt: string;
  slug: string;
  jobId: number;
  countryId: number;
  city: string;
  state?: string | null;
  postalCode?: string | null;
  address?: string | null;
  role: 'provider';
}

// Variante avec relations optionnelles (si l'API agrège ces données)
export interface ProviderWithRelations extends Provider {
  hardSkills?: HardSkill[];
  softSkills?: SoftSkill[];
  experiences?: Experience[];
  education?: Education[];
  socialLinks?: SocialLink[];
  services?: Service[];
  articles?: Article[];
  languages?: Language[];
  job?: Job;
  country?: Country;
  avatar?: string; // URL calculée à partir de profilePicture
}

// Interface pour l'affichage complet du profil
// (Supprimé) ProviderProfile: non utilisé et non souhaité

// Interface pour les options de filtrage des prestataires
export interface ProviderFilters {
  skills?: number[];          // IDs des compétences recherchées
  location?: string;          // Zone géographique
  languages?: string[];       // Langues requises
  search?: string;            // Recherche textuelle
}

// Interface pour le tri des prestataires
export interface ProviderSortOptions {
  field: 'firstName' | 'lastName' | 'location' | 'createdAt';
  direction: 'asc' | 'desc';
}

// Interface pour les statistiques des prestataires
export interface ProviderStats {
  totalProviders: number;     // Nombre total de prestataires
  topSkills: string[];        // Compétences les plus populaires
  topLocations: string[];     // Localisations les plus populaires
}

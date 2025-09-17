export interface SocialLink {
  id: number;                // Identifiant unique du lien social
  providerId: number;        // Référence au prestataire
  platform: string;          // Nom du réseau social (GitHub, LinkedIn, X, etc.)
  url: string;               // URL du profil/lien social
}


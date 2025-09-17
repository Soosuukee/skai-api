export interface Education {
  id: number;                  // Identifiant unique
  providerId: number;
  title: string;               // Titre du diplôme
  institutionName: string;    // Nom de l'institution
  description: string;        // Description de la formation
  startedAt: string;          // Date de début (ISO)
  endedAt?: string;           // Date de fin (ISO)
  institutionImage?: string;  // URL de l'image/Logo
}

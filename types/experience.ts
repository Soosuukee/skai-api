export interface Experience {
  id: number;                  // Identifiant unique
  providerId: number;
  title: string;               // Titre du poste
  companyName: string;        // Nom de l'entreprise
  firstTask: string;          // Tâche principale
  secondTask?: string;
  thirdTask?: string;
  startedAt: string;          // Date de début (ISO)
  endedAt?: string;           // Date de fin (ISO)
  companyLogo?: string;       // URL du logo
}

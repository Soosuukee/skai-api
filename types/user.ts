export interface User {
  userId: number;            // Identifiant unique de l'utilisateur
  providerId?: number;       // Référence au prestataire (optionnel)
  clientId?: number;         // Référence au client (optionnel)
  adminId?: number;          // Référence à l'administrateur (optionnel)
  email: string;             // Email de l'utilisateur
  password: string;          // Mot de passe (hashé en production)
  role: UserRole;            // Rôle de l'utilisateur
  isActive: boolean;         // Si le compte est actif
  createdAt: string;         // Date de création (format ISO)
  lastLogin: string;         // Dernière connexion (format ISO)
}

// Types de rôles utilisateur
export type UserRole = 'provider' | 'client' | 'admin';

// Interface étendue pour l'affichage avec des propriétés calculées
export interface UserWithMetadata extends User {
  displayName?: string;      // Nom d'affichage calculé
  avatar?: string;           // Avatar de l'utilisateur
  permissions?: string[];    // Permissions spécifiques
  isOnline?: boolean;        // Statut en ligne
  sessionToken?: string;     // Token de session (temporaire)
}

// Interface pour l'authentification
export interface AuthUser {
  userId: number;
  email: string;
  role: UserRole;
  providerId?: number;
  clientId?: number;
  adminId?: number;
  displayName?: string;
  avatar?: string;
  permissions?: string[];
}

// Interface pour la connexion
export interface LoginCredentials {
  email: string;
  password: string;
}

// Interface pour l'inscription
export interface RegisterData {
  email: string;
  password: string;
  role: UserRole;
  providerId?: number;
  clientId?: number;
  adminId?: number;
}

// Interface pour la réinitialisation de mot de passe
export interface PasswordReset {
  email: string;
  token?: string;
  newPassword?: string;
}

// Interface pour les options de filtrage des utilisateurs
export interface UserFilters {
  role?: UserRole;
  isActive?: boolean;
  providerId?: number;
  clientId?: number;
  adminId?: number;
  search?: string;
}

// Interface pour le tri des utilisateurs
export interface UserSortOptions {
  field: 'email' | 'role' | 'createdAt' | 'lastLogin';
  direction: 'asc' | 'desc';
}

// Interface pour les statistiques utilisateur
export interface UserStats {
  totalUsers: number;
  activeUsers: number;
  usersByRole: Record<UserRole, number>;
  recentLogins: number;
}

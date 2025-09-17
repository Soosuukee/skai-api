import { ArticleSection } from './articleSection';
import { Tag } from './tag';

export interface Article {
  articleId: number;           // Identifiant unique de l'article
  providerId: number;          // Référence au prestataire (auteur)
  languageId: number;            // Langue de l'article
  title: string;               // Titre de l'article
  slug: string;                // Slug pour l'URL
  publishedAt: string;         // Date de publication (format ISO)
  summary: string;             // Résumé de l'article
  isPublished: boolean;        // Si l'article est publié
  isFeatured: boolean;         // Si l'article est mis en avant
  cover: string;        // URL de l'image de couverture
  tags: Tag[];                 // Tag/catégorie de l'article
  sections: ArticleSection [];  // Sections de contenu
}




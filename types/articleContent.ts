import { ArticleImage } from './articleImage';

export interface ArticleContent {
  articleContentId: number;    // Identifiant unique du contenu d'article
  articleSectionId: number;    // Référence à la section d'article
  content: string;
  images: ArticleImage[];        // Type de contenu (paragraph, code, image, etc.)
}



import { ArticleContent } from './articleContent';

export interface ArticleSection {
  articleSectionId: number;    // Identifiant unique de la section d'article
  articleId: number;           // Référence à l'article
  title: string;
  content: ArticleContent [];
}



export interface Review {
  reviewId: number;
  clientId: number;
  providerId: number;
  rating: number;
  comment: string;
  createdAt: Date;
}

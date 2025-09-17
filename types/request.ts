export type RequestStatus = 'pending' | 'accepted' | 'declined';

export interface Request {
  requestId: number;
  providerId: number;
  clientId: number;
  title: string;
  description: string;
  createdAt: Date;
  status: RequestStatus;
}

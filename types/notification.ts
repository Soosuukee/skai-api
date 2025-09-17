export type recipientType= 'provider'| 'client' ;

export interface Notification {
  notificationId: number;
  recipientId: number;
  recipientType: recipientType;
  title: string;
  message: string;
  isRead: boolean;
}

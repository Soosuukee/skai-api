export type BookingStatus = 'pending' | 'accepted' | 'declined';

export interface Booking {
  id: number;
  status: BookingStatus;     // enum string
  clientId: number;
  slotId: number;            // référence à AvailabilitySlot.id
  createdAt: string;         // ISO string
}
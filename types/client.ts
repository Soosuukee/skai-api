export interface Client {
    id: number;
    firstName: string;
    lastName: string;
    email: string;
    profilePicture: string | null;
    joinedAt: string;
    slug: string;
    countryId: number;
    city: string;
    state?: string | null;
    postalCode?: string | null;
    address?: string | null;
    role: 'client';
  }
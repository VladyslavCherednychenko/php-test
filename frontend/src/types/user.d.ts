import type { UserProfile } from '@/types/profile';

export interface User {
  id: number;
  email: string;
  role: number;
  profile: UserProfile | null;
}

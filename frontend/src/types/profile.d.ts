export interface UserProfile {
  id: number;
  username: string;
  firstName: string;
  lastName: string;
  profileImage: string | null;
  bio: string;
}

export interface UserProfileForm {
  username: string;
  firstName: string;
  lastName: string;
  bio: string;
}

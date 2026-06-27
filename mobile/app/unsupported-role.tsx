import { RoleUnavailable } from '@/src/components/feedback/RoleUnavailable';

export default function UnsupportedRoleScreen() {
  return (
    <RoleUnavailable
      title="Account access unavailable"
      message="This account role is inactive or unsupported by the mobile app. Please contact the restaurant administrator."
    />
  );
}

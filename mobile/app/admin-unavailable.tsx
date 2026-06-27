import { RoleUnavailable } from '@/src/components/feedback/RoleUnavailable';

export default function AdminUnavailableScreen() {
  return (
    <RoleUnavailable
      title="Mobile admin is disabled"
      message="Full administration remains available on the Laravel web dashboard. Set EXPO_PUBLIC_ENABLE_ADMIN_MOBILE=true only when this mobile admin shell should be exposed."
    />
  );
}

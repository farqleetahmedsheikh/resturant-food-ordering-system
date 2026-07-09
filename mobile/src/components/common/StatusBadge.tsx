import { AppBadge } from './AppBadge';

type StatusBadgeProps = {
  status: string;
};

export function StatusBadge({ status }: StatusBadgeProps) {
  const normalized = status.toLowerCase();

  if (['active', 'delivered', 'paid', 'open', 'success', 'available'].includes(normalized)) {
    return <AppBadge label={status.replaceAll('_', ' ')} tone="green" />;
  }

  if (['cancelled', 'failed', 'inactive', 'disabled'].includes(normalized)) {
    return <AppBadge label={status.replaceAll('_', ' ')} tone="danger" />;
  }

  if (['pending', 'pending_payment', 'preparing', 'ready', 'assigned'].includes(normalized)) {
    return <AppBadge label={status.replaceAll('_', ' ')} tone="gold" />;
  }

  if (['accepted', 'confirmed', 'assigned_to_rider', 'picked_up', 'out_for_delivery'].includes(normalized)) {
    return <AppBadge label={status.replaceAll('_', ' ')} tone="info" />;
  }

  return <AppBadge label={status.replaceAll('_', ' ')} tone="neutral" />;
}

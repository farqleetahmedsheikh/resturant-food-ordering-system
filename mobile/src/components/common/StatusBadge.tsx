import { AppBadge } from './AppBadge';

type StatusBadgeProps = {
  status: string;
};

export function StatusBadge({ status }: StatusBadgeProps) {
  const normalized = status.toLowerCase();

  if (['delivered', 'paid', 'open', 'success'].includes(normalized)) {
    return <AppBadge label={status.replaceAll('_', ' ')} tone="green" />;
  }

  if (['cancelled', 'failed', 'inactive'].includes(normalized)) {
    return <AppBadge label={status.replaceAll('_', ' ')} tone="danger" />;
  }

  if (['pending', 'preparing'].includes(normalized)) {
    return <AppBadge label={status.replaceAll('_', ' ')} tone="gold" />;
  }

  if (['assigned_to_rider', 'out_for_delivery', 'accepted'].includes(normalized)) {
    return <AppBadge label={status.replaceAll('_', ' ')} tone="info" />;
  }

  return <AppBadge label={status.replaceAll('_', ' ')} tone="neutral" />;
}

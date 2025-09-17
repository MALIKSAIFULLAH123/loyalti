import React from 'react';
import Flag, { FlagProps } from './Flag/Flag';

export default function PendingFlag({
  variant = 'itemView',
  value,
  showTitleMobile = true
}: Pick<FlagProps, 'variant' | 'value' | 'showTitleMobile'>) {
  if (!value) return null;

  return (
    <Flag
      data-testid="pending"
      type="is_pending"
      color="white"
      variant={variant}
      value={value}
      showTitleMobile={showTitleMobile}
    />
  );
}

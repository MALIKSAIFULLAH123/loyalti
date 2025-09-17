import React from 'react';
import Flag, { FlagProps } from './Flag';

export default function FeaturedFlag({
  variant = 'itemView',
  value,
  color = 'white',
  showTitleMobile = true
}: Pick<FlagProps, 'variant' | 'value' | 'color' | 'showTitleMobile'>) {
  if (!value) return null;

  return (
    <Flag
      data-testid="featured"
      type="is_featured"
      color={color}
      variant={variant}
      value={value}
      showTitleMobile={showTitleMobile}
    />
  );
}

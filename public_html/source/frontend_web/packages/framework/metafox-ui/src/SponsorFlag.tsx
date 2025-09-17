import React from 'react';
import Flag, { FlagProps } from './Flag/Flag';

export default function SponsorFlag({
  variant = 'itemView',
  value,
  color = 'white',
  showTitleMobile = true,
  item
}: Pick<
  FlagProps,
  'variant' | 'value' | 'color' | 'showTitleMobile' | 'item'
>) {
  const isShowLabel = item?.extra?.can_show_sponsor_label !== false;

  if (!isShowLabel || !value) return null;

  return (
    <Flag
      data-testid="sponsor"
      type="is_sponsor"
      color={color}
      variant={variant}
      value={value}
      showTitleMobile={showTitleMobile}
    />
  );
}

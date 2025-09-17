import { useGlobal } from '@metafox/framework';
import { Tooltip } from '@mui/material';
import React from 'react';

export type FromNowProps = {
  value: string | number;
  component?: React.ElementType;
  className?: string;
  shorten?: boolean;
};

export default function FromNow({
  className,
  value,
  shorten,
  component: As = 'span'
}: FromNowProps) {
  const { moment } = useGlobal();

  if (!value) return null;

  const date = moment(value);
  const title = date.format('llll');

  return (
    <Tooltip title={title}>
      <As role="link" className={className} aria-label={title}>
        {date.fromNow(shorten)}
      </As>
    </Tooltip>
  );
}

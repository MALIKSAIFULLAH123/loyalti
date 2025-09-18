import { useGlobal } from '@metafox/framework';
import { Tooltip } from '@mui/material';
import React from 'react';

export type DateNowProps = {
  value: string | number;
  component?: React.ElementType;
  className?: string;
  shorten?: boolean;
  format?: string;
};

export default function DateNowStory({
  className,
  value,
  component: As = 'span',
  format = 'll'
}: DateNowProps) {
  const { moment } = useGlobal();

  if (!value) return null;

  const result = moment(value).format(format);

  const date = moment(value);
  const title = date.format('llll');

  return (
    <Tooltip title={title}>
      <As role="link" className={className} aria-label={title}>
        {result}
      </As>
    </Tooltip>
  );
}

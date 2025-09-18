import { useGlobal } from '@metafox/framework';
import { Tooltip } from '@mui/material';
import React from 'react';

export type FromNowProps = {
  value: string | number;
  component?: React.ElementType;
  className?: string;
  shorten?: boolean;
  format?: string;
  formatTooltip?: string;
};

export default function FromNowStory({
  className,
  value,
  shorten: shortenProp,
  component: As = 'span',
  formatTooltip = 'llll',
  format
}: FromNowProps) {
  const { moment } = useGlobal();

  const shorten = shortenProp ? false : true;

  if (!value) return null;

  let hour = moment(value).diff(moment(), 'minute') / 60;

  if (hour < -23 && hour >= -24) {
    hour = -23;
  }

  let result = moment
    .duration(hour * 60, 'minute')
    .humanize(shorten, { h: 24 });

  if (format && hour < -24) {
    result = moment(value).format(format);
  }

  const date = moment(value);
  const title = date.format(formatTooltip);

  return (
    <Tooltip title={title}>
      <As role="link" className={className} aria-label={title}>
        {result}
      </As>
    </Tooltip>
  );
}

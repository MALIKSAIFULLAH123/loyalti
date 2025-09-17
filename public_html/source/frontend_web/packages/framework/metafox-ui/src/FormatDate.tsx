import { useGlobal } from '@metafox/framework';
import React from 'react';
import { Tooltip } from '@mui/material';

export type FormatDateProps = {
  component?: any;
  value: string;
  format?: string;
  'data-testid': string;
  phrase?: string;
};

export default function FormatDate({
  component: As = 'time',
  value,
  format = 'llll',
  'data-testid': testid,
  phrase = '',
  tooltipFormat
}) {
  const { moment, i18n } = useGlobal();
  const text = moment(value).format(format);

  if (tooltipFormat) {
    const title = moment(value).format(tooltipFormat);

    return (
      <Tooltip title={title}>
        <As data-testid={testid}>
          {phrase
            ? i18n.formatMessage(
                { id: phrase },
                {
                  time: text
                }
              )
            : text}
        </As>
      </Tooltip>
    );
  }

  return (
    <As data-testid={testid}>
      {phrase
        ? i18n.formatMessage(
            { id: phrase },
            {
              time: text
            }
          )
        : text}
    </As>
  );
}

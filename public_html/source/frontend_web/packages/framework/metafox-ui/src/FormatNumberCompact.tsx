import React from 'react';
import { Box, Tooltip } from '@mui/material';
import { useGlobal } from '@metafox/framework';

export type FormatNumberProps = {
  value: number;
};

export default function FormatNumberCompact({
  value = 0,
  digit = 2,
  simple = false
}) {
  const { i18n } = useGlobal();
  const locale = i18n?.locale || 'en';

  if (simple) {
    return (
      <Box title={i18n.formatNumber(value)} component="span">
        {Intl.NumberFormat(locale, {
          notation: 'compact',
          maximumFractionDigits: digit,
          roundingMode: 'floor'
        }).format(value)}
      </Box>
    );
  }

  return (
    <Tooltip title={i18n.formatNumber(value)}>
      <Box component="span">
        {Intl.NumberFormat(locale, {
          notation: 'compact',
          maximumFractionDigits: digit,
          roundingMode: 'floor'
        }).format(value)}
      </Box>
    </Tooltip>
  );
}

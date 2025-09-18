/**
 * @type: ui
 * name: marketplace.ui.soldLabel
 */

import React from 'react';
import { Box, Typography, styled } from '@mui/material';
import { useGlobal } from '@metafox/framework';

const name = 'LabelSold';

const Label = styled(Box, { name, slot: 'root' })(({ theme }) => ({
  display: 'inline-flex',
  alignItems: 'center',
  justifyContent: 'center',
  padding: theme.spacing(1),
  color: 'white',
  height: theme.spacing(3),
  fontWeight: theme.typography.fontWeightBold,
  background: theme.palette.grey[700]
}));

export default function PaymentButtonItem({ label }) {
  const { i18n } = useGlobal();

  return (
    <Label>
      <Typography variant={'body2'} color={'white'}>
        {i18n.formatMessage({ id: label || 'sold' })}
      </Typography>
    </Label>
  );
}

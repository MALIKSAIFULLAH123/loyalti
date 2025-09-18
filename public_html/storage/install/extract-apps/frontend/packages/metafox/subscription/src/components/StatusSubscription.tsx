/**
 * @type: ui
 * name: subscription.ui.statusLabel
 */

import React from 'react';
import { styled } from '@mui/material';

const name = 'StatusLabelSubscription';

const Status = styled('span', {
  name,
  slot: 'packageStatus',
  shouldForwardProp: prop => prop !== 'status'
})<{ status: string }>(({ theme, status }) => ({
  color: theme.palette.error.main,
  ...(status === 'completed' && {
    color: theme.palette.success.main
  }),
  ...((status === 'pending' || status === 'init') && {
    color: theme.palette.warning.main
  })
}));

export default function StatusLabelSubscription({
  label,
  type
}: {
  label: string;
  type: string;
}) {
  return <Status status={type}>{label}</Status>;
}

import React from 'react';
import { styled } from '@mui/material';

const name = 'InvoiceStatusLabel';

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

export default function InvoiceStatusLabel({
  label,
  type
}: {
  label: string;
  type: string;
}) {
  return <Status status={type}>{label}</Status>;
}

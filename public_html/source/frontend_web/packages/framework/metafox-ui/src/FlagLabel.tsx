import React from 'react';
import { styled, Typography } from '@mui/material';

const name = 'FlagLabel';

const FlagLabel = styled(Typography, {
  name,
  slot: 'packageOuter',
  shouldForwardProp: props => props !== 'backgroundColor'
})<{ backgroundColor?: string }>(({ theme, backgroundColor }) => ({
  height: '24px',
  display: 'inline-flex',
  padding: `0 ${theme.spacing(1)}`,
  alignItems: 'center',
  justifyContent: 'center',
  backgroundColor,
  borderRadius: '4px',
  '& > *': {
    margin: '0 4px'
  }
}));

export default function InvoiceItemMainCard({ children, ...rest }) {
  return <FlagLabel {...rest}>{children}</FlagLabel>;
}

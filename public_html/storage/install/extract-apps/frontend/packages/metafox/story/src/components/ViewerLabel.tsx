import React from 'react';
import { styled, Typography } from '@mui/material';
import { LineIcon, FormatNumber } from '@metafox/ui';

const name = 'FlagLiveLabel';

const FlagLiveLabel = styled(Typography, {
  name,
  slot: 'packageOuter',
  shouldForwardProp: props => props !== 'backgroundColor'
})<{ backgroundColor?: string }>(({ theme, backgroundColor }) => ({
  height: '24px',
  display: 'inline-flex',
  padding: `0 ${theme.spacing(1)}`,
  alignItems: 'center',
  justifyContent: 'center',
  backgroundColor: 'rgba(0,0,0,0.4)',
  color: theme.palette.common.white,
  fontSize: theme.typography.body2.fontSize,
  textTransform: 'uppercase',
  borderRadius: '4px',
  '& > *': {
    margin: '0 4px'
  }
}));

export default function ViewerLabel({ total_viewer = 0 }) {
  if (!total_viewer) return null;

  return (
    <FlagLiveLabel>
      <LineIcon icon={'ico-eye-alt'} />
      <FormatNumber value={total_viewer} />
    </FlagLiveLabel>
  );
}

/**
 * @type: ui
 * name: livestreaming.ui.durationTime
 * chunkName: livestreamingUi
 */
import React from 'react';
import { styled, Typography } from '@mui/material';

const name = 'DurationTime';

const FlagLiveLabel = styled(Typography, {
  name,
  slot: 'DurationTime'
})<{ backgroundColor?: string }>(({ theme }) => ({
  height: '24px',
  display: 'inline-flex',
  padding: `0 ${theme.spacing(1)}`,
  alignItems: 'center',
  justifyContent: 'center',
  backgroundColor: 'rgba(0,0,0,0.4)',
  color: theme.palette.common.white,
  fontSize: theme.typography.body2.fontSize,
  borderRadius: '4px',
  '& > *': {
    margin: '0 4px'
  }
}));

export default function FlagLiveLabelItem({ time, ...rest }) {
  if (!time) return null;

  return <FlagLiveLabel {...rest}>{time}</FlagLiveLabel>;
}

/**
 * @type: ui
 * name: livestreaming.ui.labelLive
 */
import React from 'react';
import { styled, Typography } from '@mui/material';
import { useGlobal } from '@metafox/framework';

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
  backgroundColor: backgroundColor || theme.palette.error.main,
  color: theme.palette.common.white,
  fontSize: theme.typography.body2.fontSize,
  fontWeight: 500,
  textTransform: 'uppercase',
  borderRadius: '4px',
  '& > *': {
    margin: '0 4px'
  }
}));

export default function FlagLiveLabelItem({ children, ...rest }) {
  const { i18n } = useGlobal();

  return (
    <FlagLiveLabel {...rest}>
      {children || i18n.formatMessage({ id: 'live' })}
    </FlagLiveLabel>
  );
}

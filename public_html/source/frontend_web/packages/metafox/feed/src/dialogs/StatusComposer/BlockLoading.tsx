import { CircularProgress, Box } from '@mui/material';
import { styled } from '@mui/material/styles';
import React from 'react';

const BlockLoadingBackdrop = styled('div', { name: 'BlockLoading' })(
  ({ theme }) => ({
    position: 'absolute',
    left: 0,
    top: 0,
    bottom: 0,
    right: 0,
    zIndex: '9999',
    color: theme.palette.text.secondary,
    background:
      theme.palette.mode === 'light'
        ? 'rgba(255, 255, 255, 0.6) !important'
        : 'rgba(0, 0, 0, 0.3) !important'
  })
);

const BlockLoadingContent = styled(Box, {
  name: 'BlockLoading',
  slot: 'Content'
})({
  position: 'absolute',
  left: '50%',
  top: '50%',
  marginLeft: -20,
  marginTop: -20
});

export default function BlockLoadingComponent() {
  return (
    <BlockLoadingBackdrop>
      <BlockLoadingContent>
        <CircularProgress color="inherit" size={40} />
      </BlockLoadingContent>
    </BlockLoadingBackdrop>
  );
}

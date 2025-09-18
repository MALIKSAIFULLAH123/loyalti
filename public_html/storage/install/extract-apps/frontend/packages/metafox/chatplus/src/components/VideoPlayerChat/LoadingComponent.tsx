import { CircularProgress, Box } from '@mui/material';
import { styled } from '@mui/material/styles';
import React from 'react';

const name = 'loading-video';

const BackDrop = styled(Box, { name, slot: 'backdrop' })(({ theme }) => ({
  position: 'absolute',
  left: 0,
  top: 0,
  bottom: 0,
  right: 0,
  zIndex: 10
}));

const ContentStyled = styled(Box, { name, slot: 'conent' })(({ theme }) => ({
  position: 'absolute',
  left: '50%',
  top: '50%',
  marginLeft: -20,
  marginTop: -20
}));

export default function LoadingComponent({ size = 40 }: { size?: number }) {
  return (
    <BackDrop data-testid="loadingIndicator">
      <ContentStyled>
        <CircularProgress color="primary" size={size} />
      </ContentStyled>
    </BackDrop>
  );
}

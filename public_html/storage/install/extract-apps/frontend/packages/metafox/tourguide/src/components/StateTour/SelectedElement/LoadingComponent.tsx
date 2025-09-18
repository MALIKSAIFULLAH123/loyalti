import { Box, Skeleton, styled } from '@mui/material';
import React from 'react';

const name = 'LoadingComponent';

const RootStyled = styled(Box, { name, slot: 'Root' })(({ theme }) => ({
  minWidth: 500,
  padding: theme.spacing(2)
}));
const HeaderStyled = styled(Box, { name, slot: 'Header' })(({ theme }) => ({
  borderBottom: theme.mixins.border('secondary'),
  paddingBottom: theme.spacing(1)
}));
const ContentStyled = styled(Box, { name, slot: 'Content' })(({ theme }) => ({
  paddingTop: theme.spacing(2)
}));

function LoadingComponent(): JSX.Element {
  return (
    <RootStyled>
      <HeaderStyled>
        <Skeleton animation="wave" height={50} width="40%" />
      </HeaderStyled>
      <ContentStyled>
        <Skeleton variant="text" height={50} width="100%" />
        <Skeleton variant="rounded" height={120} width="100%" />
        <Skeleton variant="text" height={50} width="100%" />
        <Skeleton variant="text" height={50} width="100%" />
      </ContentStyled>
    </RootStyled>
  );
}

export default LoadingComponent;

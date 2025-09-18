import React from 'react';
import { Box, Skeleton, styled } from '@mui/material';

const RootStyled = styled(Box)(({ theme }) => ({
  width: '100%',
  height: '100%'
}));

const ContentStyled = styled(Box)(({ theme }) => ({
  display: 'flex',
  alignItems: 'center'
}));

const TextStyled = styled(Box)(({ theme }) => ({
  display: 'flex',
  flexDirection: 'column',
  flex: 1,
  minHeight: 0
}));

const SkeletonLoading = () => {
  return (
    <RootStyled>
      <ContentStyled>
        <Skeleton
          variant="circular"
          width={40}
          height={40}
          sx={{ mr: 2, alignSelf: 'flex-start', mt: 1 }}
        />
        <TextStyled>
          <Skeleton width={'60px'} height={'40px'} variant="text" />
          <Skeleton width={'80%'} height={'40px'} variant="text" />
        </TextStyled>
      </ContentStyled>
    </RootStyled>
  );
};

export default SkeletonLoading;

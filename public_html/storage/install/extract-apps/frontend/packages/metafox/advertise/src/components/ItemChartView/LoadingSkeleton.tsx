import React from 'react';
import { Box, Skeleton, styled } from '@mui/material';
import { useGlobal } from '@metafox/framework';

const ContentStyled = styled(Box)(({ theme }) => ({
  height: '145px',
  width: '100%',
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center'
}));

const SkeletonLoading = () => {
  const { jsxBackend } = useGlobal();

  return (
    <Box>
      <ContentStyled>
        {jsxBackend.render({ component: 'form.DefaultLoading' })}
      </ContentStyled>
      <Box sx={{ display: 'flex', alignItems: 'center' }}>
        <Skeleton variant="circular" width={30} height={30} sx={{ mr: 1 }} />
        <Skeleton width={'100%'} height={'40px'} variant="text" />
      </Box>
    </Box>
  );
};

export default SkeletonLoading;

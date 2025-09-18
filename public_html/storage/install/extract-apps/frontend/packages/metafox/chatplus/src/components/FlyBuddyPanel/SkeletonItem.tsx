import { ItemMedia } from '@metafox/ui';
import { Box, Skeleton, styled } from '@mui/material';
import React from 'react';

const RootStyled = styled(Box, {
  name: 'LayoutSlot',
  slot: 'onlineItem',
  overridesResolver(props, styles) {
    return [styles.onlineItem];
  }
})(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  padding: theme.spacing(1)
}));

const ContentStyled = styled(Box)(({ theme }) => ({
  flex: 1,
  minWidth: 0,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'space-between'
}));

function SkeletonItem() {
  return (
    <Box mt={1}>
      {Array(4)
        .fill(0)
        .map((_, index) => (
          <RootStyled key={index}>
            <ItemMedia>
              <Skeleton
                variant="avatar"
                width={32}
                height={32}
                sx={{ mr: 1 }}
              />
            </ItemMedia>
            <ContentStyled>
              <Skeleton variant="text" width={'80%'} />
              <Skeleton variant="avatar" width={8} height={8} />
            </ContentStyled>
          </RootStyled>
        ))}
    </Box>
  );
}

export default SkeletonItem;

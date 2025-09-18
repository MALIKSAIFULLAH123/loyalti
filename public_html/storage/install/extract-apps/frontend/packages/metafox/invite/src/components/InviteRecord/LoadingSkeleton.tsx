/**
 * @type: skeleton
 * name: invite.itemView.inviteRecord.skeleton
 */

import React from 'react';
import { Box, Checkbox, Skeleton, styled } from '@mui/material';
import { ItemView } from '@metafox/ui';
import { useGlobal } from '@metafox/framework';

const ItemViewStyled = styled(Box)(({ theme }) => ({
  flex: 1,
  display: 'flex',
  alignItems: 'center',

  padding: theme.spacing(1, 2),
  background: theme.palette.background.default,
  borderRadius: theme.shape.borderRadius,
  marginLeft: theme.spacing(2)
}));

const SkeletonLoading = ({ wrapAs, wrapProps }) => {
  const { useIsMobile } = useGlobal();

  const isMobile = useIsMobile();

  if (isMobile) {
    return <Skeleton width={'100%'} height={'48px'} variant="text" />;
  }

  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps} testid="item-advertise">
      <Box>
        <Checkbox disabled color="primary" size="medium" />
      </Box>
      <ItemViewStyled>
        <Skeleton
          width={'100%'}
          sx={{ bgcolor: 'transparent' }}
          animation="wave"
        />
      </ItemViewStyled>
    </ItemView>
  );
};

export default SkeletonLoading;

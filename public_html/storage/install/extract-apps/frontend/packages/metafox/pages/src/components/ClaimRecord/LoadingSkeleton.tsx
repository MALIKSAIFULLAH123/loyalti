/**
 * @type: skeleton
 * name: page.itemView.claimRecord.skeleton
 */

import React from 'react';
import { Skeleton } from '@mui/material';
import { ItemView } from '@metafox/ui';
import { useGlobal } from '@metafox/framework';

const SkeletonLoading = ({ wrapAs, wrapProps }) => {
  const { useIsMobile } = useGlobal();
  const isMobile = useIsMobile();

  if (isMobile) {
    return <Skeleton width={'100%'} height={'48px'} variant="text" />;
  }

  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps} testid="item-claim">
      <Skeleton
        width={'100%'}
        sx={{ bgcolor: 'transparent' }}
        animation="wave"
      />
    </ItemView>
  );
};

export default SkeletonLoading;

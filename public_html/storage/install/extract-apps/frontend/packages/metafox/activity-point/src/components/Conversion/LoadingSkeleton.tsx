/**
 * @type: skeleton
 * name: activitypoint.itemView.conversion.skeleton
 */

import React from 'react';
import { Skeleton } from '@mui/material';
import { ItemView } from '@metafox/ui';

const SkeletonLoading = ({ wrapAs, wrapProps }) => {
  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps} testid="item-transaction">
      <Skeleton
        width={'100%'}
        sx={{ bgcolor: 'transparent' }}
        animation="wave"
      />
    </ItemView>
  );
};

export default SkeletonLoading;

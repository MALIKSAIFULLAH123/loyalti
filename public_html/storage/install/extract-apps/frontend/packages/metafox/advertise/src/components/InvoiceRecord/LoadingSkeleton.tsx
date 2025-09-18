/**
 * @type: skeleton
 * name: advertise.itemView.invoiceRecord.skeleton
 * chunkName: advertise
 */

import React from 'react';
import { Skeleton } from '@mui/material';
import { ItemView } from '@metafox/ui';

const SkeletonLoading = ({ wrapAs, wrapProps }) => {
  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps} testid="item-invoice">
      <Skeleton
        width={'100%'}
        sx={{ bgcolor: 'transparent' }}
        animation="wave"
      />
    </ItemView>
  );
};

export default SkeletonLoading;

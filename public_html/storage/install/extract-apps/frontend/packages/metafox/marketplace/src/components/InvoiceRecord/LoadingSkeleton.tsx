/**
 * @type: skeleton
 * name: marketplace_invoice.itemView.invoice.skeleton
 */

import React from 'react';
import { Skeleton } from '@mui/material';
import { ItemView } from '@metafox/ui';

const SkeletonLoading = ({ wrapAs, wrapProps }) => {
  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps} testid="invoice">
      <Skeleton
        width={'100%'}
        sx={{ bgcolor: 'transparent' }}
        animation="wave"
      />
    </ItemView>
  );
};

export default SkeletonLoading;

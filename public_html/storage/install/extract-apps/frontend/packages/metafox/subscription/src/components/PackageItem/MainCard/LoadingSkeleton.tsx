/**
 * @type: skeleton
 * name: subscription_package.itemView.mainCard.skeleton
 */

import {
  ImageSkeleton,
  ItemMedia,
  ItemText,
  ItemView,
  ItemTitle
} from '@metafox/ui';
import { Skeleton } from '@mui/material';
import React from 'react';

export default function LoadingSkeleton(props) {
  return (
    <ItemView {...props}>
      <ItemMedia>
        <ImageSkeleton ratio="169" />
      </ItemMedia>
      <ItemText sx={{ padding: '24px' }}>
        <ItemTitle>
          <Skeleton width={'100%'} />
        </ItemTitle>
        <div>
          <Skeleton width={160} />
        </div>
      </ItemText>
    </ItemView>
  );
}

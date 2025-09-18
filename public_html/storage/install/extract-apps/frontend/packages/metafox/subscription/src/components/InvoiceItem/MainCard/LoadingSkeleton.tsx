/**
 * @type: skeleton
 * name: subscription_invoice.itemView.mainCard.skeleton
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
        <ImageSkeleton ratio="11" />
      </ItemMedia>
      <ItemText>
        <ItemTitle>
          <Skeleton width={'100%'} />
        </ItemTitle>
        <div>
          <Skeleton width={160} />
          <Skeleton width={160} />
          <Skeleton width={160} />
        </div>
      </ItemText>
    </ItemView>
  );
}

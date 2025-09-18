/**
 * @type: skeleton
 * name: blog.itemView.mainCard.skeleton
 */
import { useIsMobile } from '@metafox/framework';
import {
  ImageSkeleton,
  ItemMedia,
  ItemSummary,
  ItemText,
  ItemTitle,
  ItemView
} from '@metafox/ui';
import { Skeleton } from '@mui/material';
import React from 'react';

export default function LoadingSkeleton(props) {
  const isMobile = useIsMobile();

  return (
    <ItemView {...props}>
      <ItemMedia>
        <ImageSkeleton ratio={isMobile ? '169' : '11'} borderRadius={0} />
      </ItemMedia>
      <ItemText>
        <div>
          <Skeleton width={160} />
        </div>
        <ItemTitle>
          <Skeleton width={'100%'} />
        </ItemTitle>
        <ItemSummary>
          <Skeleton width={160} />
        </ItemSummary>
        <div>
          <Skeleton width={160} />
        </div>
      </ItemText>
    </ItemView>
  );
}

/**
 * @type: skeleton
 * name: sevent.itemView.mapCard.skeleton
 */
import { useIsMobile } from '@metafox/framework';
import {
  ImageSkeleton,
  ItemMedia,
  ItemText,
  ItemTitle,
  ItemView
} from '@metafox/ui';
import { Skeleton } from '@mui/material';
import React from 'react';

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  const isMobile = useIsMobile();

  return (
    <ItemView testid="skeleton" wrapAs={wrapAs} wrapProps={wrapProps}>
      <ItemMedia>
        <ImageSkeleton ratio={!isMobile ? '169' : '11'} borderRadius={0} />
      </ItemMedia>
      <div style={{ display: 'flex', gap: '8px', marginTop: '6px', alignItems: 'flex-start' }}>
        <ItemText>
           <ItemTitle>
            <Skeleton width={'100%'} />
          </ItemTitle>
          <div>
            <Skeleton width={160} />
          </div>
          <div>
              <Skeleton style={{ transform: 'initial', margin: '8px 0' }} width={'100%'} height='52px' />
          </div>
          <div>
              <Skeleton width={'100%'} />
          </div>
        </ItemText>
      </div>
    </ItemView>
  );
}

/**
 * @type: skeleton
 * name: sevent.itemView.mainCard.skeleton
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
        <ImageSkeleton ratio={!isMobile ? '169' : '11'} style={{
          borderBottomLeftRadius: '8px',
          borderBottomRightRadius: '8px'
        }} />
      </ItemMedia>
      <div style={{ display: 'flex', gap: '8px', minHeight: '100px', marginTop: '8px', alignItems: 'flex-start' }}>
        <ItemText>
           <ItemTitle style={{ marginBottom: '13px' }}>
            <Skeleton width={'100%'} />
          </ItemTitle>
          <div>
            <Skeleton width={160} />
          </div>
        </ItemText>
      </div>
      <div>
        <Skeleton width={'100%'} />
      </div>
    </ItemView>
  );
}

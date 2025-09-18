/**
 * @type: skeleton
 * name: sevent_ticket.itemView.mainCard.skeleton
 */
import {
  ImageSkeleton,
  ItemSummary,
  ItemText,
  ItemTitle,
  ItemView
} from '@metafox/ui';
import Grid from '@mui/material/Grid';
import { Skeleton } from '@mui/material';
import React from 'react';

export default function LoadingSkeleton({ wrapAs, wrapProps }) {

  return (
    <ItemView testid="skeleton" wrapAs={wrapAs} wrapProps={wrapProps}>
      <Grid container spacing={2}>
        <Grid item xs={12} md={4}>
            <ImageSkeleton ratio={'32'} borderRadius={0} />
        </Grid>
      <Grid item xs={12} md={8}>
      <ItemText style={{ display: 'flex', gap: '4px', flexDirection: 'column' }}>
          <ItemTitle>
            <Skeleton width={'100%'} />
          </ItemTitle>
          <ItemSummary>
            <Skeleton width={160} />
          </ItemSummary>
          <div>
            <Skeleton width={160} />
          </div>
          <div>
            <Skeleton width={160} />
          </div>
      </ItemText>
      </Grid>
      </Grid>
    </ItemView>
  );
}

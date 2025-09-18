/**
 * @type: skeleton
 * name: sevent.itemView.userTicketCard.skeleton
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
    <ItemView testid="skeleton" wrapAs={wrapAs} wrapProps={wrapProps} style={{ padding: '16px' }}>
      <Grid container spacing={2}>
        <Grid item xs={2}>
            <ImageSkeleton ratio={'32'} borderRadius={0} />
        </Grid>
      <Grid item xs={10}>
      <ItemText style={{ display: 'flex', gap: '2px', flexDirection: 'column' }}>
          <ItemTitle>
            <Skeleton width={'100%'} />
          </ItemTitle>
          <ItemSummary>
            <Skeleton width={160} />
          </ItemSummary>
          <div>
            <Skeleton width={160} />
          </div>
          <div style={{ display: 'flex', alignItems: 'flex-start',
             gap: '16px', marginTop: '8px', justifyContent: 'flex-start' }}>
            <Skeleton width={'94px'} height={'32px'} style={{ transform: 'initial'
           }} />
            <Skeleton width={'147px'} height={'32px'} style={{ transform: 'initial'
          }} />
          </div>
      </ItemText>
      </Grid>
      </Grid>
    </ItemView>
  );
}
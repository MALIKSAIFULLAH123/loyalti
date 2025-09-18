/**
 * @type: skeleton
 * name: quiz.itemView.AnswerPercent.skeleton
 */
import { ItemView, ItemTitle } from '@metafox/ui';
import { Skeleton } from '@mui/material';
import React from 'react';

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps}>
      <ItemTitle>
        <Skeleton width={160} />
      </ItemTitle>
      <Skeleton width={'100%'} />
    </ItemView>
  );
}

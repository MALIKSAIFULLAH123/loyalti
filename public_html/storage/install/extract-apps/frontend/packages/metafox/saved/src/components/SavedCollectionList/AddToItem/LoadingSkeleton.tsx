/**
 * @type: skeleton
 * name: saved_collection_list.itemView.addToItem.skeleton
 * title: Add To Item Skeleton
 */
import {
  ItemMedia,
  ItemText,
  ItemTitle,
  ItemView,
  LineIcon
} from '@metafox/ui';
import { Skeleton, styled } from '@mui/material';
import React from 'react';

const LineIconStyled = styled(LineIcon)(({ theme }) => ({
  fontSize: '15px',
  color: theme.palette.text.hint
}));

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps} testid="LoadingSkeleton">
      <ItemMedia>
        <LineIconStyled icon="ico-square-o" color="textPrimary" />
      </ItemMedia>
      <ItemText>
        <ItemTitle>
          <Skeleton variant="text" width="50%" height="20" />
        </ItemTitle>
      </ItemText>
    </ItemView>
  );
}

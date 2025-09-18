/**
 * @type: skeleton
 * name: music_song.itemView.profileCard.skeleton
 */
import {
  ImageSkeleton,
  ItemMedia,
  ItemText,
  ItemView,
  ItemTitle
} from '@metafox/ui';
import { Box, Skeleton, styled } from '@mui/material';
import * as React from 'react';

const Title = styled(ItemTitle, {
  name: 'Title'
})(({ theme }) => ({
  [theme.breakpoints.down('sm')]: {
    marginBottom: `${theme.spacing(1)} !important`,
    marginRight: `${theme.spacing(0)} !important`
  }
}));

const ItemStatistic = styled(Box, {
  name: 'ItemStatistic'
})(({ theme }) => ({
  display: 'flex'
}));

export default function LoadingSkeleton({ wrapAs, wrapProps }) {

  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps}>
      <ItemMedia>
        <ImageSkeleton ratio={'11'} borderRadius={0} />
      </ItemMedia>
      <ItemText>
        <Title>
          <Skeleton width={'100%'} />
        </Title>
        <ItemStatistic>
          <Skeleton width={160} />
        </ItemStatistic>
      </ItemText>
    </ItemView>
  );
}

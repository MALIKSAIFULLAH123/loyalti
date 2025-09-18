/**
 * @type: skeleton
 * name: music_album.itemView.mainCard.skeleton
 */

import {
  ImageSkeleton,
  ItemMedia,
  ItemText,
  ItemTitle,
  ItemView
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

const ItemMinor = styled(Box, {
  name: 'ItemMinor'
})(({ theme }) => ({
  color: theme.palette.text.secondary,
  fontSize: 13,
  lineHeight: 1.2,
  marginBottom: theme.spacing(1.2),
  paddingRight: theme.spacing(2),
  overflow: 'hidden',
  maxWidth: '100%',
  whiteSpace: 'nowrap',
  textOverflow: 'ellipsis',
  '& a': {
    color: theme.palette.text.secondary
  }
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
        <ItemMinor>
          <Skeleton width={160} />
        </ItemMinor>
        <ItemStatistic>
          <Skeleton width={160} />
        </ItemStatistic>
      </ItemText>
    </ItemView>
  );
}

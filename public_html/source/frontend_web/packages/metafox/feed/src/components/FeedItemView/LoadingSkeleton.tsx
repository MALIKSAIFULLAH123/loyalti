/**
 * @type: skeleton
 * name: feed.itemView.mainCard.skeleton
 */
import { ItemView } from '@metafox/ui';
import { Skeleton, Box, styled } from '@mui/material';
import React from 'react';
import useStyles from './styles';
import { AvatarWrapper } from './FeedItemView';

const FeedItemHeader = styled('div', {
  name: 'FeedItem',
  slot: 'feedItemHeader',
  overridesResolver(props, styles) {
    return [styles.feedItemHeader];
  }
})(({ theme }) => ({
  display: 'flex',
  flexDirection: 'row',
  marginBottom: theme.spacing(1.5),
  '& button:hover': {
    backgroundColor: theme.palette.action.selected
  }
}));

export function LoadingSkeleton({ wrapAs, wrapProps }) {
  const classes = useStyles();

  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps}>
      <FeedItemHeader>
        <AvatarWrapper>
          <Skeleton variant="circular" width={40} height={40} />
        </AvatarWrapper>
        <div className={classes.headerInfo}>
          <div>
            <Skeleton variant="text" component="div" />
          </div>
          <div className={classes.privacyBlock}>
            <Skeleton variant="text" width={120} />
          </div>
        </div>
      </FeedItemHeader>
      <div className={classes.contentSkeleton}>
        <Box sx={{ height: 200 }}></Box>
        <Box
          sx={{
            display: 'flex',
            justifyContent: 'space-between',
            alignItems: 'center'
          }}
        >
          <Skeleton variant="text" width={150} />
          <Skeleton variant="text" width={100} />
        </Box>
      </div>
    </ItemView>
  );
}

export default LoadingSkeleton;

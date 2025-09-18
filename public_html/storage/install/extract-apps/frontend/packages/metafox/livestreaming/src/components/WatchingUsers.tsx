/**
 * @type: ui
 * name: livestreaming.ui.watchingUsers
 */
import React from 'react';
import { styled, Typography, Box } from '@mui/material';
import { connectItem, useGlobal } from '@metafox/framework';
import { UserAvatar } from '@metafox/ui';
import { keyframes } from '@emotion/react';
import {
  useFirestoreDocIdListener,
  useFirebaseFireStore
} from '@metafox/framework/firebase';

const animationKeyFrame = keyframes`
    0% {transform: translateY(100%);}
    100% {transform: translateY(0);}
`;

const name = 'WatchingViewer';

const AnimationWrapper = styled(Box, {
  name,
  slot: 'animationWrapper',
  shouldForwardProp: prop => prop !== 'isOwner'
})<{ spinner?: boolean }>(({ theme, spinner }) => ({
  display: 'inline-flex',
  animation: `${animationKeyFrame} 1s forwards`
}));
const Wrapper = styled(Box, {
  name,
  slot: 'Wrapper'
})(({ theme }) => ({
  display: 'block',
  overflow: 'hidden'
}));

function LiveWatchingUsers({ streamKey, identity }) {
  const { i18n, usePrevious } = useGlobal();
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  const [indexRender, setIndexRender] = React.useState(0);
  const [hidden, setHidden] = React.useState(false);
  const db = useFirebaseFireStore();
  const refTimeout = React.useRef<null | ReturnType<typeof setTimeout>>();
  const viewerData = useFirestoreDocIdListener(db, {
    collection: 'live_video_view',
    docID: streamKey
  });
  const viewerListing = viewerData?.view || [];
  const prevLength = usePrevious(viewerListing.length);
  const newLength = viewerListing.length;

  React.useEffect(() => {
    if (newLength && indexRender + 1 >= newLength) {
      // is last item, will hide
      refTimeout.current = setTimeout(() => {
        setHidden(true);
      }, 2000);

      return;
    }

    // render 1 item per 2s
    setHidden(false);
    clearTimeout(refTimeout.current);
    refTimeout.current = setTimeout(() => {
      setIndexRender(prev => prev + 1);
    }, 2000);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [indexRender]);

  React.useEffect(() => {
    // update render when has change viewerListing
    if (hidden && newLength !== prevLength) {
      if (newLength > prevLength) {
        setHidden(false);
      }

      setIndexRender(viewerListing.length - 1);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [viewerListing?.length]);

  if (hidden || !streamKey || !viewerListing?.length) return null;

  const dataRender = viewerListing[indexRender];

  if (!dataRender) return null;

  return (
    <Wrapper pt={1}>
      <AnimationWrapper key={`${dataRender?.id}`} sx={{ display: 'flex' }}>
        <Box mr={1}>
          <UserAvatar user={dataRender as any} size={24} />
        </Box>
        <Box>
          <Typography component={'span'} variant={'body1'}>
            {i18n.formatMessage(
              { id: 'user_joined' },
              {
                user_name: dataRender?.full_name
              }
            )}
          </Typography>
        </Box>
      </AnimationWrapper>
    </Wrapper>
  );
}

export default connectItem(LiveWatchingUsers);

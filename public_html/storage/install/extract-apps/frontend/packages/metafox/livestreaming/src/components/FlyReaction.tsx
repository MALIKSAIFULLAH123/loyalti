/**
 * @type: ui
 * name: livestreaming.ui.flyReaction
 */
import React from 'react';
import { styled, Typography, Box } from '@mui/material';
import { useGlobal } from '@metafox/framework';
import { keyframes } from '@emotion/react';
import {
  useFirestoreDocIdListener,
  useFirebaseFireStore
} from '@metafox/framework/firebase';
import { ReactionLiveType } from '../types';

const animationKeyFrame = keyframes`
    0% {
        bottom:0;
        opacity: 1;
    }
    30% {
        transform:translateX(30px);
        bottom: 30%;
        opacity: 1
    }
    70% {
       transform:translateX(0px);
       bottom: 70%;
       opacity: 1
    }
    100% {
        transform:translateX(30px);
        bottom: 100%;
        opacity: 0;
    }
`;

const name = 'FlyReaction';

const ReactionIcon = styled(Box, {
  name,
  slot: 'ReactionIcon',
  shouldForwardProp: props => props !== 'index'
})<{ index?: number }>(({ theme, index }) => ({
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  position: 'absolute',
  animation: `${animationKeyFrame} linear 2s forwards `,
  animationDelay: `${Math.floor(Math.random() * 99) * 20}ms`,
  left: `calc(50% - ${Math.max(Math.floor(Math.random() * 10) * 10, 30)}%)`,
  bottom: '-32px',
  width: '24px',
  height: '24px',
  '& img': {
    width: '100%',
    height: '100%'
  }
}));
const Wrapper = styled(Typography, {
  name,
  slot: 'Wrapper',
  shouldForwardProp: props => props !== 'backgroundColor'
})<{ backgroundColor?: string }>(({ theme }) => ({
  position: 'absolute',
  left: '24px',
  bottom: 0,
  width: '100px',
  height: '70%',
  pointerEvents: 'none'
}));

function FlyReaction({ streamKey, identity }) {
  const { dispatch } = useGlobal();
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  const db = useFirebaseFireStore();
  const likeData: ReactionLiveType = useFirestoreDocIdListener(db, {
    collection: 'live_video_like',
    docID: streamKey
  });
  const reactionListing = likeData?.like || [];
  const data = reactionListing.slice(Math.max(reactionListing.length - 20, 0));

  React.useEffect(() => {
    dispatch({
      type: 'livestreaming/updateStatistic',
      payload: {
        identity,
        most_reactions_information: likeData?.most_reactions_information || [],
        statistic: likeData?.statistic || {
          total_like: likeData?.total_like
        }
      }
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [
    likeData?.total_like,
    likeData?.statistic,
    likeData?.most_reactions_information
  ]);

  if (!streamKey || !data?.length) return null;

  return (
    <Wrapper>
      {data.map(({ reaction: { icon, title, id } }, index) => (
        <ReactionIcon key={`live_reaction_${index}`} index={index}>
          <img src={icon} alt={title} />
        </ReactionIcon>
      ))}
    </Wrapper>
  );
}

export default FlyReaction;

/**
 * @type: ui
 * name: story.ui.flyReaction
 * chunkName: storyDetail
 */

import React from 'react';
import { styled, Typography, Box } from '@mui/material';
import { keyframes } from '@emotion/react';
import { isEmpty } from 'lodash';
import { useGetItem } from '@metafox/framework';

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
  // animationDelay: `${Math.floor(Math.random() * 99) * 20}ms`,
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
  left: '50%',
  transform: 'translate(-50%,0)',
  bottom: 0,
  width: '120px',
  height: '70%',
  pointerEvents: 'none'
}));

interface Props {
  identity: string;
  data: any[];
}

function FlyReaction({ identity, data }: Props) {
  const item = useGetItem(identity);

  if (isEmpty(data) || isEmpty(item)) return null;

  return (
    <Wrapper>
      {data.map((reaction, index) => (
        <ReactionIcon key={`story_reaction_${index}`} index={index}>
          <img src={reaction?.src} alt={reaction?.title} />
        </ReactionIcon>
      ))}
    </Wrapper>
  );
}

export default React.memo(
  FlyReaction,
  (prev, next) =>
    prev?.identity === next?.identity &&
    prev.data?.length === next?.data?.length
);

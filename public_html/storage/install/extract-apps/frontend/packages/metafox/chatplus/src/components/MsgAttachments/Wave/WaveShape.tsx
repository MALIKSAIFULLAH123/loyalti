import { Box, styled } from '@mui/material';
import React from 'react';
import SvgShape from './SvgShape';

const name = 'WaveShape';

const RootStyled = styled(Box, { name, slot: 'Root' })(({ theme }) => ({
  position: 'relative',
  zIndex: 2,
  overflow: 'hidden',
  maxWidth: '85px'
}));

const Track = styled(Box, {
  name,
  slot: 'Track',
  shouldForwardProp: props => props !== 'left'
})<{ left?: number; isDraggingProgress?: boolean }>(
  ({ theme, left, isDraggingProgress }) => ({
    position: 'absolute',
    display: 'block',
    width: '4px',
    height: '44px',
    borderTopLeftRadius: '2px',
    borderTopRightRadius: '2px',
    borderBottomLeftRadius: '2px',
    borderBottomRightRadius: '2px',
    background: '#fff',
    transition: 'all 200ms linear',
    top: '50%',
    transform: 'translate(0, -50%)',
    left: left ? `${left}%` : 0,
    ...(isDraggingProgress && {
      transition: 'none'
    })
  })
);

interface Props {
  isOwner?: boolean;
  progress?: number;
  audioRef?: any;
  [key: string]: any;
}

function WaveShape({ isOwner, progress, ...rest }: Props) {
  return (
    <RootStyled>
      <SvgShape isOwner={isOwner} percent={progress} {...rest} />
      {progress === 0 || progress === 100 ? null : (
        <Track
          isDraggingProgress={rest?.isDraggingProgress?.current}
          left={progress}
        ></Track>
      )}
    </RootStyled>
  );
}

export default WaveShape;

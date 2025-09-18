import { useGlobal } from '@metafox/framework';
import useScrollEnd from '@metafox/story/blocks/StoryListing/useScrollEnd';
import { styled, Box } from '@mui/material';
import React from 'react';

const name = 'slider_story';

const RootStyled = styled(Box, {
  name,
  slot: 'root',
  shouldForwardProp: props => props !== 'isMobile'
})<{ isMobile?: boolean }>(({ theme, isMobile }) => ({
  maxHeight: '182px',
  display: 'flex',
  alignItems: 'center',
  ...(isMobile && {
    overflowX: 'auto',
    overflowY: 'hidden',
    MsOverflowStyle: 'none',
    scrollbarWidth: 'none',
    scrollBehavior: 'smooth',
    '&::-webkit-scrollbar': {
      display: 'none'
    }
  })
}));

const Wrapper = styled(Box, {
  name,
  slot: 'Wrapper',
  shouldForwardProp: props => props !== 'translate' && props !== 'isMobile'
})<{ translate?: any; isMobile?: boolean }>(({ translate, isMobile }) => ({
  display: 'flex',
  flexWrap: 'nowrap',
  alignItems: 'center',
  height: '100%',
  transform: `translate(${isMobile ? 0 : translate}px, 0)`,
  transition: 'all 300ms ease'
}));

const THROTTLE = 45;
const ITEM_WIDTH = 108;

interface Props {
  children: React.ReactNode;
  translate: any;
  setTranslate?: any;
  loadMore?: any;
}

export default function Slider({
  children,
  translate,
  setTranslate,
  loadMore
}: Props) {
  const { useIsMobile } = useGlobal();

  const isMobile = useIsMobile();
  const containerRef = React.useRef(null);
  const wrapperRef = React.useRef(null);

  let startX = 0;
  let endX = 0;
  let isMouseDown = false;

  const handleMouseDown = e => {
    isMouseDown = true;
    startX = e.clientX;
  };

  const handleMouseMove = e => {
    if (!isMouseDown) return;

    endX = e.clientX;
  };

  const handleMouseUp = e => {
    if (isMobile || !isMouseDown) return;

    // prevent click item
    if (endX === 0) return;

    const diffX = endX - startX;

    const container = containerRef.current?.getBoundingClientRect();
    const wrapper = wrapperRef.current?.getBoundingClientRect();

    if (Math.abs(diffX) > 30) {
      if (diffX > 0) {
        // Swipe Right

        if (translate < ITEM_WIDTH + THROTTLE) {
          setTranslate(0);

          return;
        }

        const x = translate - ITEM_WIDTH * 2;
        setTranslate(x);
      } else {
        // Swipe Left

        loadMore();

        if (translate > wrapper?.width - container?.width) {
          return;
        }

        const x = translate + ITEM_WIDTH * 2;
        setTranslate(x);
      }

      e.preventDefault();
      e.stopPropagation();
    }

    isMouseDown = false;
  };

  useScrollEnd(isMobile && loadMore ? loadMore : undefined, containerRef);

  React.useEffect(() => {
    if (!isMobile) return;

    // mobile
    if (translate < containerRef.current.clientWidth - ITEM_WIDTH) {
      containerRef.current.scrollLeft = 0;

      return;
    }

    containerRef.current.scrollLeft = translate;
  }, [translate, isMobile]);

  return (
    <RootStyled ref={containerRef} isMobile={isMobile}>
      <Wrapper
        isMobile={isMobile}
        translate={-translate}
        onMouseDown={handleMouseDown}
        onMouseMove={handleMouseMove}
        onMouseUp={handleMouseUp}
        onMouseLeave={handleMouseUp}
        ref={wrapperRef}
      >
        {children}
      </Wrapper>
    </RootStyled>
  );
}

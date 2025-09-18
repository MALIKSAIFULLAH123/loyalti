import { LineIcon } from '@metafox/ui';
import { styled, Box } from '@mui/material';
import React from 'react';
import useScrollEnd from './useScrollEnd';
import { camelCase } from 'lodash';

const name = 'slider_story';

const Slider = styled(Box, {
  name
})(() => ({
  width: '100%',
  position: 'relative'
}));

const MainSlider = styled(Box, {
  name,
  shouldForwardProp: prop => prop !== 'isMobile'
})<{ isMobile?: boolean }>(({ theme, isMobile }) => ({
  width: '100%',
  overflow: 'hidden',
  position: 'relative',
  ...(isMobile && {
    overflowX: 'auto',
    MsOverflowStyle: 'none',
    scrollbarWidth: 'none',
    scrollBehavior: 'smooth',
    '&::-webkit-scrollbar': {
      display: 'none'
    }
  })
}));

const Wrapper = styled(Box, { name })(({ theme }) => ({
  display: 'inline-flex',
  width: 'auto',
  transition: 'all 500ms ease'
}));

const ArrowNav = styled(Box, {
  name: 'Slider',
  slot: 'arrowNav',
  overridesResolver(props, styles) {
    return [styles.arrowNav];
  },
  shouldForwardProp: props => props !== 'isShowAvatar' && props !== 'hide'
})<{ isShowAvatar?: boolean; hide?: boolean }>(({ theme, isShowAvatar }) => ({
  zIndex: 99,
  background: `${theme.palette.background.paper}!important`,
  color: `${theme.palette.text.secondary}!important`,
  border: theme.mixins.border('secondary'),
  width: '48px',
  height: '48px',
  display: 'inline-flex',
  alignItems: 'center',
  justifyContent: 'center',
  borderRadius: '100%',
  position: 'absolute',
  top: '50%',
  cursor: 'pointer',
  '& span': {
    fontWeight: theme.typography.fontWeightSemiBold,
    fontSize: theme.mixins.pxToRem(18)
  },
  ...(isShowAvatar && {
    width: '36px',
    height: '36px'
  })
}));

interface Props {
  loadMoreLoading?: boolean;
  loadMore?: () => void;
  children: React.ReactNode;
  showArrow: boolean;
  ended?: boolean;
  isShowAvatar?: boolean;
  isMobile?: boolean;
  total?: any;
}

const ArrowNext = props => {
  if (props?.hide) return null;

  return (
    <ArrowNav {...props}>
      <LineIcon icon="ico-angle-right" />
    </ArrowNav>
  );
};

const ArrowPrev = props => {
  if (props?.hide) return null;

  return (
    <ArrowNav {...props}>
      <LineIcon icon="ico-angle-left" />
    </ArrowNav>
  );
};

const THROTTE_SLIDE = 250;

export default function StorySlider({
  children,
  loadMore,
  showArrow: showArrowProps = true,
  ended,
  isMobile,
  total,
  isShowAvatar
}: Props) {
  const sliderRef = React.useRef<any>();
  const wrapperRef = React.useRef<any>();
  const [transform, setTransform] = React.useState(0);
  const fit = sliderRef.current?.clientWidth || 0;
  const expand = wrapperRef.current?.scrollWidth || 0;
  const [showArrow, setShowArrow] = React.useState(false);

  const STEP_SLIDE = fit / 2;

  const refResize = React.useRef<ResizeObserver>();

  const onResize = () => {
    const fit = sliderRef.current?.clientWidth || 0;
    const expand = wrapperRef.current?.scrollWidth || 0;
    setShowArrow(expand > fit);
  };

  React.useEffect(() => {
    if (!sliderRef.current) return;

    refResize.current = new ResizeObserver(() => {
      onResize();
    });

    refResize.current?.observe(sliderRef.current);

    return () => refResize?.current.disconnect(); // clean up
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [onResize, sliderRef.current, total]);

  React.useEffect(() => {
    if (!showArrow) return;

    if (expand - fit - transform < 0) {
      setTransform(expand - fit);
    }
  }, [transform, fit, expand, showArrow]);

  const arrowClickNext = () => {
    setTransform(prev => {
      const scroll = expand - prev - fit;

      if (scroll < STEP_SLIDE + THROTTE_SLIDE) return prev + scroll;

      const plus = Math.min(scroll, STEP_SLIDE);

      if (plus < 0) return prev;

      if (prev < 0) return plus;

      return prev + plus;
    });
  };

  const arrowClickPrev = () => {
    setTransform(prev => {
      if (prev < STEP_SLIDE + THROTTE_SLIDE) return 0;

      return Math.max(prev - STEP_SLIDE, 0);
    });
  };
  useScrollEnd(isMobile && loadMore ? loadMore : undefined, sliderRef);

  React.useEffect(() => {
    if (isMobile) return;

    if (!wrapperRef && !wrapperRef.current) return;

    if (!ended && (expand - transform) / 2 <= fit) {
      loadMore && loadMore();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [wrapperRef.current, loadMore, ended, expand, transform, fit, isMobile]);

  return (
    <Slider data-testid={camelCase('storySlider')}>
      <MainSlider ref={sliderRef} isMobile={isMobile}>
        <Wrapper
          ref={wrapperRef}
          sx={{ transform: `translateX(-${transform}px)` }}
        >
          {children}
        </Wrapper>
      </MainSlider>
      {showArrowProps && showArrow && !isMobile ? (
        <Box>
          <ArrowPrev
            data-testid={camelCase('storySliderArrowPrev')}
            sx={{ left: 12, transform: 'translate(0,-50%)' }}
            hide={transform <= 0}
            onClick={arrowClickPrev}
            isShowAvatar={isShowAvatar}
          />
          <ArrowNext
            data-testid={camelCase('storySliderArrowNext')}
            sx={{ right: 12, transform: 'translate(0,-50%)' }}
            onClick={arrowClickNext}
            hide={expand - fit - transform <= 0}
            isShowAvatar={isShowAvatar}
          />
        </Box>
      ) : null}
    </Slider>
  );
}

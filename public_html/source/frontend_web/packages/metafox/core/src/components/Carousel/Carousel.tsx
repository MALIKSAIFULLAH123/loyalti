import React from 'react';
import useEmblaCarousel from 'embla-carousel-react';
import { Box, styled, SxProps } from '@mui/material';
import { OptionsType } from './types';
import { EmblaCarouselType } from '@metafox/core';

const name = 'CoreCarousel';

const Root = styled(Box, { name, slot: 'root' })(({ theme }) => ({
  overflow: 'hidden'
}));
const Container = styled(Box, {
  name,
  slot: 'container',
  shouldForwardProp: props => props !== 'gap'
})<{ gap?: number }>(({ theme, gap }) => ({
  display: 'flex',
  alignItems: 'flex-start',
  gridColumnGap: theme.spacing(gap || 1),
  '& > *': {
    flex: '0 0 100%',
    minWidth: 0
  }
}));

type CarouselProps = {
  children: React.ReactNode;
  sxContainer?: SxProps;
  sx?: SxProps;
  options?: OptionsType;
  plugins?: any;
  gap?: number;
  onInit?: (emblaApi: EmblaCarouselType) => void;
};

function Carousel({
  children,
  sx,
  sxContainer,
  options = {},
  gap = 1,
  plugins = [],
  onInit
}: CarouselProps) {
  const [carouselRef, carouselApi] = useEmblaCarousel(options, plugins);
  const refMounted = React.useRef(false);

  React.useEffect(() => {
    if (!carouselApi || refMounted.current || !onInit) return;

    refMounted.current = true;
    const onInitHandle = () => onInit(carouselApi);

    if (carouselApi) {
      onInit(carouselApi);
    }

    carouselApi.on('init', onInitHandle);

    return () => {
      carouselApi.off('init', onInitHandle);
    };
  }, [carouselApi, onInit]);

  if (!children) return null;

  return (
    <Root ref={carouselRef} sx={sx}>
      <Container gap={gap} sx={sxContainer}>
        {children}
      </Container>
    </Root>
  );
}

export default React.memo(Carousel);

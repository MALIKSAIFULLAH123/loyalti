import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import React from 'react';
import { ComponentCarouselProps } from '../types';
import { IconButton, styled } from '@mui/material';
import { camelCase } from 'lodash';

const NextButton = styled(IconButton, {
  name: 'NextButton'
})(({ theme }) => ({
  color: theme.palette.primary.main
}));

function NextButtonCarousel({
  carousel,
  size = 'medium',
  ...restProps
}: ComponentCarouselProps) {
  const { useTheme } = useGlobal();
  const { direction } = useTheme() || {};

  const { onNext, nextBtnDisabled } = carousel;

  return (
    <NextButton
      data-testid={camelCase('button next Button Carousel')}
      onClick={onNext}
      size={size}
      disabled={nextBtnDisabled}
      {...restProps}
    >
      <LineIcon
        icon={direction === 'rtl' ? 'ico-angle-left' : 'ico-angle-right'}
      />
    </NextButton>
  );
}

export default React.memo(NextButtonCarousel);

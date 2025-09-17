import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import React from 'react';
import { ComponentCarouselProps } from '../types';
import { IconButton, styled } from '@mui/material';
import { camelCase } from 'lodash';

const PrevButton = styled(IconButton, {
  name: 'PrevButton'
})(({ theme }) => ({
  color: theme.palette.primary.main
}));

function PrevButtonCarousel({
  carousel,
  size = 'medium',
  ...restProps
}: ComponentCarouselProps) {
  const { useTheme } = useGlobal();
  const { direction } = useTheme() || {};

  const { onPrev, prevBtnDisabled } = carousel;

  return (
    <PrevButton
      data-testid={camelCase('button Prev Button Carousel')}
      onClick={onPrev}
      size={size}
      disabled={prevBtnDisabled}
      {...restProps}
    >
      <LineIcon
        icon={direction === 'rtl' ? 'ico-angle-right' : 'ico-angle-left'}
      />
    </PrevButton>
  );
}

export default React.memo(PrevButtonCarousel);

import { Box, styled } from '@mui/material';
import React from 'react';
import { ComponentCarouselProps } from '../types';

const SelectedCount = styled(Box, {
  name: 'SelectedCount'
})(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(15),
  color: theme.palette.text.secondary,
  minWidth: '40px',
  textAlign: 'center'
}));

interface Props extends ComponentCarouselProps {
  total?: number;
}

function CountDisplayCarousel({
  total: totalProp,
  carousel,
  component: As = Box,
  ...restProps
}: Props) {
  const { currentSelected, total: totalCarousel } = carousel;

  const total = totalProp || totalCarousel;

  return (
    <SelectedCount component={As} {...restProps}>
      {currentSelected + 1}/{total}
    </SelectedCount>
  );
}

export default React.memo(CountDisplayCarousel);

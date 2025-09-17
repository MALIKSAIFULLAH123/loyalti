import React, { useCallback, useState } from 'react';
import { EmblaCarouselType, useCarouselType } from '../types';

export default function useCarousel(carouselApi): useCarouselType {
  const [carouselState, setCarouselState] = useState({
    currentSelected: 0,
    total: 0,
    prevBtnDisabled: true,
    nextBtnDisabled: false
  });

  const updateCarouselState = useCallback((carouselApi: EmblaCarouselType) => {
    setCarouselState({
      total: carouselApi.scrollSnapList().length,
      currentSelected: carouselApi.selectedScrollSnap(),
      prevBtnDisabled: !carouselApi.canScrollPrev(),
      nextBtnDisabled: !carouselApi.canScrollNext()
    });
  }, []);

  React.useEffect(() => {
    if (!carouselApi) return;

    updateCarouselState(carouselApi);
    const onSelect = () => updateCarouselState(carouselApi);
    const onReInit = () => updateCarouselState(carouselApi);
    carouselApi.on('select', onSelect);
    carouselApi.on('reInit', onReInit);

    return () => {
      carouselApi.off('select', onSelect);
      carouselApi.off('reInit', onReInit);
    };
  }, [carouselApi, updateCarouselState]);

  const onPrev = useCallback(() => {
    if (!carouselApi) return;

    carouselApi.scrollPrev();
  }, [carouselApi]);

  const onNext = useCallback(() => {
    if (!carouselApi) return;

    carouselApi.scrollNext();
  }, [carouselApi]);

  return {
    ...carouselState,
    carouselApi,
    onPrev,
    onNext
  };
}

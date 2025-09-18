import React from 'react';
import { TourGuideContext } from '../context';
import { TourGuideContextProps, TourGuideItemShape } from '../types';
import { useGlobal } from '@metafox/framework';
import { APP_NAME, RESOURCE_TOURGUIDE } from '../constants';

export { default as useStatusCreate } from './useStatusCreate';

export default function useTourGuideContext(): TourGuideContextProps {
  return React.useContext(TourGuideContext);
}

export function useGetTourGuide(id) {
  const { useGetItem } = useGlobal();

  const identity = `${APP_NAME}.entities.${RESOURCE_TOURGUIDE}.${id}`;

  const item = useGetItem(identity);

  return item as TourGuideItemShape | undefined;
}

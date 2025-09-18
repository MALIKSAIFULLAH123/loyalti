import { TourGuideContextProps } from '../types';

export { default as TourGuideContext } from './TourGuideContext';

export { reducerTourGuide } from './reducerTourGuide';

export const initStateTourGuide: TourGuideContextProps = {
  status: 0,
  createStep: 0,
  tourId: undefined,
  step: 0,
  totalStep: 0,
  steps: [],
  pauseStatus: 0,
  pageParams: {},
  isMoveDock: false,
  initialStep: 0,
  hasDragDock: false
};

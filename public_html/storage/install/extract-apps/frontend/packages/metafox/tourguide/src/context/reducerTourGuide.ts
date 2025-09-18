import {
  PauseStatus,
  StatusTourGuide,
  TourGuideContextProps,
  TourGuideStep,
  ValueStepSubmitType
} from '../types';
import produce, { Draft } from 'immer';

type Action =
  | {
      type: 'setUpdate';
      payload: TourGuideContextProps;
    }
  | { type: 'setStatusCreate'; payload: TourGuideStep }
  | { type: 'resetDock' }
  | { type: 'resetStart' }
  | { type: 'setStep'; payload: { step?: number } }
  | { type: 'setPlayPause'; payload: PauseStatus }
  | { type: 'setMoveDock'; payload: boolean }
  | { type: 'setValueStepSubmit'; payload: ValueStepSubmitType };

export const reducerTourGuide = produce(
  (draft: Draft<TourGuideContextProps>, action: Action) => {
    switch (action.type) {
      case 'setUpdate':
        draft = Object.assign(draft, action.payload);
        break;

      case 'setStatusCreate':
        draft.createStep = action.payload;
        break;

      case 'resetDock':
        draft.status = StatusTourGuide.No;
        draft.createStep = TourGuideStep.Init;
        draft.tourId = undefined;
        draft.isMoveDock = false;
        draft.initialStep = 0;
        draft.hasDragDock = false;
        break;

      case 'resetStart':
        draft.step = 0;
        draft.totalStep = 0;
        draft.initialStep = 0;
        draft.steps = [];
        draft.tourId = undefined;
        break;

      case 'setStep':
        draft.step = action.payload?.step;
        break;

      case 'setPlayPause':
        draft.pauseStatus = action.payload;
        break;
      case 'setMoveDock':
        draft.isMoveDock = action.payload;

        if (action.payload) {
          draft.hasDragDock = true;
        }

        break;
      case 'setValueStepSubmit':
        draft.valueStepSubmit = action.payload;
        break;
    }
  }
);

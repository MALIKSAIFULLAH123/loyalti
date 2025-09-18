import { createReducer } from '@reduxjs/toolkit';
import { Draft } from 'immer';
import { AppState, StatusTourGuide, TourGuideStep } from '../types';

type State = Draft<AppState['statusTourguide']>;

export default createReducer<State>(
  {
    tourguide_id: null,
    status: StatusTourGuide.No,
    createStep: TourGuideStep.Init
  },
  builder => {
    builder.addCase(
      'tourguide/reducer/updateStatus',
      (draft: State, action: any) => {
        draft.status = action?.payload?.status;
        draft.createStep = action?.payload?.createStep;
        draft.tourguide_id = action?.payload?.tourguide_id;
      }
    );

    builder.addCase(
      'tourguide/reducer/playing',
      (draft: State, action: any) => {
        draft.status = action?.payload?.status;
        draft.tourguide_id = action?.payload?.tourguide_id;
      }
    );
  }
);

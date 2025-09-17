import produce, { Draft } from 'immer';
import { State } from './types';

const reducerFunction = produce((draft: Draft<State>, action) => {
  switch (action.type) {
    case 'next':
      if (draft.activeStep > draft.data.steps.length - 1) return;

      draft.activeStep = draft.activeStep + 1;
      draft.activeId = draft.data.steps[draft.activeStep]?.id;
      break;
    case 'back':
      if (draft.activeStep < 1) return;

      draft.activeStep = draft.activeStep - 1;
      draft.activeId = draft.data.steps[draft.activeStep]?.id;
      break;
  }
});

export default reducerFunction;

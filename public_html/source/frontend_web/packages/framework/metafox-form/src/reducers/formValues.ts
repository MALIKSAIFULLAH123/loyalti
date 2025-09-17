/**
 * @type: reducer
 * name: formValues
 */

import { createReducer, Draft } from '@reduxjs/toolkit';

type State = Record<string, any>;

export default createReducer<State>({}, builder => {
  builder.addCase('formValues/onChange', (state: Draft<State>, action: any) => {
    const { formName, data } = action.payload;

    if (formName) {
      state[formName] = data;
    }
  });
  builder.addCase(
    'formValues/multiForm/nextStep',
    (state: Draft<State>, action: any) => {
      const { formName, data, processChildId } = action.payload;

      state[formName] = {
        ...state[formName],
        [processChildId]: data
      };
    }
  );
  builder.addCase(
    'formValues/onDestroy',
    (state: Draft<State>, action: any) => {
      const { formName } = action.payload;

      if (formName) {
        state[formName] = undefined;
      }
    }
  );
});

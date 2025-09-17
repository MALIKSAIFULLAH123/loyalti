/**
 * @type: reducer
 * name: _resourceMenus
 */
import { createReducer } from '@reduxjs/toolkit';
import { ACTION_LOAD_SETTING } from '../state/constants';

type State = {};

type UpdateAction = {
  type: any;
  payload: any;
};

export default createReducer<State>({}, builder => {
  builder.addCase(
    ACTION_LOAD_SETTING,
    (_: State, { payload }: UpdateAction) => payload.resourceMenus
  );
});

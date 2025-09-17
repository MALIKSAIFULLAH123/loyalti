import { AppState } from '../types';
import { createSlice } from '@reduxjs/toolkit';

type State = Partial<AppState['accountSettings']>;

export const accountSettingsSlice = createSlice({
  name: 'accountSettingsSlice',
  initialState: {},
  reducers: {
    getSettingFulfilled: (state: State, action) => {
      state.data = action.payload;
      state.loaded = true;
    },
    updateFulfilled: (state: State, action) => {
      Object.keys(action.payload).forEach(
        item => (state.data[item] = action.payload[item])
      );
    },
    updateRejected: (state: State) => state,
    updatePending: (state: State) => state
  }
});

export const {
  getSettingFulfilled,
  updateFulfilled,
  updateRejected,
  updatePending
} = accountSettingsSlice.actions;

export default accountSettingsSlice.reducer;

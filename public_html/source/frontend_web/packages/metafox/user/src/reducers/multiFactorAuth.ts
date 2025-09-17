import { AppState } from '../types';
import { createSlice } from '@reduxjs/toolkit';

type State = Partial<AppState['multiFactorAuthSettings']>;

export const mfaSettingsSlice = createSlice({
  name: 'mfaSettingsSlice',
  initialState: {},
  reducers: {
    getMFASettingFulfilled: (state: State, action) => {
      state.data = action.payload;
      state.loaded = true;
    },
    updateActiveMFA: (state: State, action) => {
      if (!action?.payload) return;

      const { is_active, service } = action.payload;

      const index = state.data.findIndex(item => service === item.service);

      state.data[index].is_active = is_active;
    }
  }
});

export const { getMFASettingFulfilled, updateActiveMFA } =
  mfaSettingsSlice.actions;

export default mfaSettingsSlice.reducer;

import { AppState } from '../types';
import { createSlice } from '@reduxjs/toolkit';

type State = Partial<AppState['paymentSettings']>;

export const paymentSettingsSlice = createSlice({
  name: 'paymentSettingsSlice',
  initialState: {},
  reducers: {
    getPaymentSettingFulfilled: (state: State, action) => {
      state.data = action.payload;
      state.loaded = true;
    }
  }
});

export const { getPaymentSettingFulfilled } = paymentSettingsSlice.actions;

export default paymentSettingsSlice.reducer;

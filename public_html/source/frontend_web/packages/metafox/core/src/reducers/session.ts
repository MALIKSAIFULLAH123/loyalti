/**
 * @type: reducer
 * name: session
 */
import { createSlice } from '@reduxjs/toolkit';

const slice = createSlice({
  name: 'session',
  initialState: {
    user: {},
    loggedIn: false,
    accounts: [],
    loaded: false,
    meta: {}
  },
  reducers: {
    login(state, action) {
      state.user = action.payload;
      state.loggedIn = true;
    },
    update(state, action) {
      state.user = action.payload?.user;
      state.meta = action.payload?.meta;
      state.loggedIn = true;
    },
    logout(state) {
      state.user = null;
      state.meta = null;
      state.loggedIn = false;
    },
    setAccounts(state, action) {
      state.accounts = action.payload;
    },
    done(state, action) {
      state.loaded = true;
    }
  }
});

export const { login, logout } = slice.actions;

export default slice.reducer;

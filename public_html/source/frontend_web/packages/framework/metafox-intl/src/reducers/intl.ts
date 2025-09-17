/**
 * @type: reducer
 * name: intl
 */
import { createAction, createReducer } from '@reduxjs/toolkit';
import { AppState } from '@metafox/intl/types';

const setMessages = createAction('intl/setMessages');

export default createReducer<AppState>(
  {
    messages: {}
  },
  builder => {
    builder.addCase(setMessages, (state, action) => {
      state.messages = action.payload;
    });
  }
);

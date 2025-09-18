import { createReducer } from '@reduxjs/toolkit';
import { Draft } from 'immer';
import { AppState } from '../types';

type State = Draft<AppState['newChatRoom']>;

export default createReducer<State>(
  {
    searchText: '',
    searching: true,
    collapsed: false,
    results: {
      users: []
    }
  },
  builder => {
    // toggle panel
    builder.addCase(
      'chatplus/newChatRoom/togglePanel',
      (state: State, action: any) => {
        state.collapsed = !state.collapsed;
      }
    );

    // search results.
    builder.addCase(
      'chatplus/newChatRoom/search/FULFILL',
      (state: State, action: any) => {
        state.results = action.payload;
      }
    );

    // update results users
    builder.addCase(
      'chatplus/newChatRoom/search/updateUsers',
      (state: State, action: any) => {
        state.results.users = action.payload;
      }
    );
  }
);

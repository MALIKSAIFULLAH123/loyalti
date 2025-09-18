import { createReducer } from '@reduxjs/toolkit';
import { Draft } from 'immer';
import { isEmpty } from 'lodash';
import { AppState, InitResultShape } from '../types';

type State = Draft<AppState['spotlight']>;

export default createReducer<State>(
  {
    searchText: '',
    loading: false,
    users: [],
    rooms: []
  },
  builder => {
    builder.addCase('chatplus/init', (state: State, action: any) => {
      const friends: InitResultShape['friends'] = action.payload?.friends;

      if (isEmpty(friends)) return;

      const listFriend = Object.values(friends)
        ? Object.values(friends)?.slice(0, 4)
        : [];

      state.users = listFriend;
    });

    builder.addCase(
      'chatplus/spotlight/search/FULFILL',
      (_: State, action: any) => action.payload
    );
    builder.addCase(
      'chatplus/spotlight/clearSearching',
      (draft: State, action: any) => {
        draft.searchText = '';
      }
    );
  }
);

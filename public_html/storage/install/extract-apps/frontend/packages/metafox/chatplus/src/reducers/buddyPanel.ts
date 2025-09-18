import { createReducer, Draft } from '@reduxjs/toolkit';
import { AppState } from '../types';

type State = Draft<AppState['buddyPanel']>;

export default createReducer<AppState['buddyPanel']>(
  {
    searchText: '',
    searching: false,
    onlineSearching: false,
    collapsed: true
  },
  builder => {
    // toggle search box
    builder.addCase(
      'chatplus/buddyPanel/togglePanel',
      (draft: State, action: any) => {
        draft.collapsed = !draft.collapsed;
      }
    ); // toggle search box
    builder.addCase(
      'chatplus/buddyPanel/toggleSearching',
      (draft: State, action: any) => {
        draft.searching = !draft.searching;
      }
    );
    builder.addCase(
      'chatplus/buddyPanel/toggleOnlineSearching',
      (draft: State, action: any) => {
        draft.onlineSearching = !draft.onlineSearching;
      }
    );
    builder.addCase(
      'chatplus/buddyPanel/searching',
      (draft: State, action: any) => {
        const { searchText } = action.payload;
        draft.searchText = searchText;
      }
    );

    // clear searching
    builder.addCase(
      'chatplus/buddyPanel/clearSearching',
      (draft: State, action: any) => {
        draft.searchText = '';
      }
    ); // close searching
    builder.addCase(
      'chatplus/buddyPanel/closeSearching',
      (draft: State, action: any) => {
        draft.searching = false;
        draft.searchText = '';
      }
    );

    builder.addCase(
      'chatplus/buddyPanel/openSearching',
      (draft: State, action: any) => {
        draft.searching = true;
      }
    );
  }
);

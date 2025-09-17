/**
 * Combine all reducers in this file and export the combined reducers.
 */

import { combineReducers } from '@reduxjs/toolkit';
import { InjectedReducersType } from '../types';

/**
 * Merges the main reducer with the router state and dynamically injected reducers
 * Initially we don't have any injectedReducers, so returning identity function to avoid the error
 */
export default function createReducer(
  injectedReducers: InjectedReducersType = {}
): any {
  if (0 === Object.keys(injectedReducers).length) {
    return (state: any) => state;
  } else {
    return combineReducers({
      ...injectedReducers
    });
  }
}

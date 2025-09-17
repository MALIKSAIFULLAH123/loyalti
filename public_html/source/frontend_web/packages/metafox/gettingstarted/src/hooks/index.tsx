import { getPagingSelector, GlobalState } from '@metafox/framework';
import { useSelector } from 'react-redux';
import { PAGING_ID_TODO_LIST } from '../constants';
import { AppState } from '../types';

export const getStepListing = (state: GlobalState) =>
  state['getting-started'].stepListing;

export function useStepListing() {
  return useSelector<GlobalState, AppState['stepListing']>(state =>
    getStepListing(state)
  );
}

export function usePagingDataTodoList() {
  return useSelector((state: GlobalState) =>
  getPagingSelector(state, PAGING_ID_TODO_LIST)
);
}

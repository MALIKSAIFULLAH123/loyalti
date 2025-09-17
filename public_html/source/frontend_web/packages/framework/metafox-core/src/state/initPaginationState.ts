import { PagingState } from '@metafox/framework';

export default function initPagingState(): PagingState {
  return {
    ids: [],
    page: 0,
    initialized: false,
    pages: {},
    ended: false,
    loadedToPage: 0,
    dirty: false,
    loading: false,
    refreshing: false,
    offset: {}
  };
}

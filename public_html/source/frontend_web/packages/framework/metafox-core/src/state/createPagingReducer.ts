import { PagingState } from '@metafox/framework';
import produce from 'immer';
import { difference, isArray, isString, uniq, pick, isEmpty } from 'lodash';
import qs from 'query-string';
import {
  ENTITY_DELETE,
  LOGGED_OUT,
  PAGINATION_CLEAR,
  PAGINATION_FAILED,
  PAGINATION_PUSH,
  PAGINATION_REFRESH,
  PAGINATION_FULFILL_PAGE,
  PAGINATION_RESET_ALL,
  PAGINATION_START,
  PAGINATION_SUCCESS,
  PAGINATION_UNSHIFT,
  PAGINATION_UN_LIST,
  PAGINATION_MODIFIED,
  PAGINATION_PUSH_INDEX,
  PAGINATION_DELETE,
  PAGINATION_SWAP
} from '../constants';
import initPagingState from './initPaginationState';

export default function createPagingReducer() {
  return produce((draft, action) => {
    switch (action.type) {
      case PAGINATION_MODIFIED: {
        const { domains, excludes } = action.payload;
        const domainModified = domains.replace('/', '');
        const excludesModified = excludes?.replace('/', '');

        Object.keys(draft).forEach(pageId => {
          if (
            pageId.indexOf(excludesModified) < 0 &&
            (draft[pageId]?.domains?.indexOf(domainModified) >= 0 ||
              domainModified?.indexOf(draft[pageId]?.domains) >= 0)
          ) {
            draft[pageId].dirty = true;
            draft[pageId].ended = false;
          }
        });

        break;
      }
      case PAGINATION_RESET_ALL:
        return {};
      case LOGGED_OUT:
        return {};
      case PAGINATION_UN_LIST:
      case ENTITY_DELETE: {
        const { identity, pagingId } = action.payload;

        if (pagingId) {
          if (draft[pagingId]?.ids.includes(identity))
            draft[pagingId].ids = difference(draft[pagingId].ids, [identity]);

          return;
        }

        Object.keys(draft).forEach(pageId => {
          if (draft[pageId]?.ids.includes(identity)) {
            draft[pageId].ids = difference(draft[pageId].ids, [identity]);

            if (!draft[pageId].ids.length) {
              draft[pageId].dirty = true;
            }
          }
        });
        break;
      }
      case PAGINATION_DELETE: {
        const { identity, prefixPagingId } = action.payload;

        if (prefixPagingId) {
          Object.keys(draft).forEach(pageId => {
            if (pageId.startsWith(prefixPagingId)) {
              if (draft[pageId]?.ids.includes(identity)) {
                draft[pageId].ids = difference(draft[pageId].ids, [identity]);
              }
            }
          });

          return;
        }

        Object.keys(draft).forEach(pageId => {
          if (draft[pageId]?.ids.includes(identity)) {
            draft[pageId].ids = difference(draft[pageId].ids, [identity]);
          }
        });
        break;
      }
      case PAGINATION_REFRESH: {
        const pagingId = action.payload?.pagingId;

        const prev: PagingState = initPagingState();

        prev.refreshing = true;
        draft[pagingId] = prev;

        break;
      }

      case PAGINATION_FULFILL_PAGE: {
        // handle fulfil page only type pagination
        const pagingId = action.payload?.pagingId;
        const currentPage = action.payload?.currentPage;

        const prev: PagingState = draft[pagingId] || initPagingState();

        if (prev.paginationType !== 'pagination') return;

        prev.refreshing = true;
        prev.dirty = false;
        const page = currentPage || prev.page;

        if (page && prev.pages[page]) {
          prev.pages = pick(prev.pages, [page]);
        } else {
          prev.pages = {};
        }

        draft[pagingId] = prev;

        break;
      }

      case PAGINATION_START: {
        const paging = action.payload?.paging;

        if (!paging) return;

        const { pagingId } = paging;

        if (!pagingId) return;

        const prev: PagingState = draft[pagingId] || initPagingState();

        prev.loading = true;
        prev.refreshing = false;
        draft[pagingId] = prev;
        break;
      }

      case PAGINATION_FAILED: {
        const paging = action.payload?.paging;

        if (!paging) return;

        const { pagingId, ended } = paging;

        if (!pagingId) return;

        const prev: PagingState = draft[pagingId] || initPagingState();

        prev.loading = false;
        prev.error = action.payload?.error;
        prev.ended = ended;
        prev.refreshing = false;
        draft[pagingId] = prev;
        break;
      }

      case PAGINATION_CLEAR: {
        const { pagingId, prefixPagingId } = action.payload;

        if (draft[pagingId]) {
          draft[pagingId] = undefined;
        }

        if (prefixPagingId) {
          Object.keys(draft).forEach(pageId => {
            if (pageId.startsWith(prefixPagingId)) {
              draft[pageId] = undefined;
            }
          });
        }

        break;
      }
      case PAGINATION_SUCCESS: {
        const paging = action.payload?.paging;

        if (!paging) return;

        const { ids, pagingId, page, ended, pages } = paging;

        if (!pagingId) return;

        const urlParsed = qs.parseUrl(pagingId).url.replace('/', '');

        const prev: PagingState = draft[pagingId] || initPagingState();

        prev.initialized = true;
        prev.ids = ids;
        prev.page = page;
        prev.loading = false;
        prev.ended = ended;
        prev.domains = urlParsed;
        prev.offset = paging.offset;
        prev.pagesOffset = paging.pagesOffset;
        prev.paginationType = paging.paginationType;
        prev.noResultProps = paging.noResultProps;
        prev.pages = { ...prev.pages, ...pages };

        draft[pagingId] = prev;
        break;
      }
      case PAGINATION_UNSHIFT: {
        const { data, pagingId } = action.payload;

        if (isString(pagingId))
          draft[pagingId].ids = uniq([...data, ...draft[pagingId].ids]);

        if (isArray(pagingId))
          pagingId.forEach(id => {
            if (!draft[id]) return;

            draft[id].ids = uniq([...data, ...draft[id].ids]);
          });

        break;
      }
      case PAGINATION_PUSH_INDEX: {
        const { data, pagingId, indexId } = action.payload;

        if (isString(pagingId)) {
          draft[pagingId].ids?.splice(indexId, 0, ...data);
          draft[pagingId].ids = uniq(draft[pagingId].ids);
        }

        if (isArray(pagingId)) {
          pagingId.forEach((id, index) => {
            if (!draft[id]) return;

            draft[id].ids?.splice(
              isArray(indexId) ? indexId[index] : 0,
              0,
              ...data
            );
            draft[id].ids = uniq(draft[id].ids);
          });
        }

        break;
      }
      case PAGINATION_PUSH: {
        const { data, pagingId, currentPage } = action.payload;
        const pagingIdArray = isString(pagingId)
          ? [pagingId]
          : isArray(pagingId)
          ? pagingId
          : [];

        if (isEmpty(pagingIdArray)) return;

        pagingIdArray.forEach(id => {
          if (!draft[id]) return;

          const prev: PagingState = draft[id];

          if (prev?.paginationType === 'pagination') {
            const { pagesOffset } = prev || {};
            const dataPage = prev.pages[currentPage];
            pagesOffset.total_item = pagesOffset.total_item + 1;

            if (dataPage && dataPage.ids?.length < pagesOffset?.per_page) {
              // is last page and last page has space to add item
              prev.pages[currentPage].ids = uniq([...dataPage.ids, ...data]);
            } else {
              // keep current page and reset other page
              prev.pages = pick(prev.pages, [currentPage]);
            }

            draft[id] = prev;
          } else {
            draft[id].ids = uniq([...draft[id].ids, ...data]);
          }
        });
        break;
      }

      case PAGINATION_SWAP: {
        const { indexSwap, indexSwapped, pagingId } = action.payload;

        if (indexSwap === indexSwapped) return;

        const temp = draft[pagingId].ids[indexSwap];
        draft[pagingId].ids[indexSwap] = draft[pagingId].ids[indexSwapped];
        draft[pagingId].ids[indexSwapped] = temp;
        break;
      }
    }
  }, {});
}

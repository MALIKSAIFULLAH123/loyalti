import { PAGINGID_SAVED_LIST_DATA } from './constant';

export const getPagingIdListSaved = pageParams => {
  let result = PAGINGID_SAVED_LIST_DATA;

  if (pageParams?.collection_id) {
    result += `-${pageParams?.collection_id}`;
  }

  if (pageParams?.type) {
    result += `-${pageParams?.type}`;
  }

  return result;
};

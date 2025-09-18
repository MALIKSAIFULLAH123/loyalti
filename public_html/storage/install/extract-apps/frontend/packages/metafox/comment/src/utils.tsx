import { SortTypeValue, SortTypeModeValue } from '@metafox/comment/types';
import {
  SORT_OLDEST,
  SORT_RELEVANT,
  SORT_MODE_ASC,
  SORT_MODE_DESC
} from '@metafox/comment';
import { uniq } from 'lodash';

export const getValueSortTypeMode = (type: SortTypeValue) =>
  ([SORT_OLDEST, SORT_RELEVANT].includes(type) ? SORT_MODE_ASC : SORT_MODE_DESC);

export const getKeyDataBySortTypeMode = (mode: SortTypeModeValue) => {
  const keyPrefix = '_data_';

  return `${keyPrefix}_${mode}_related_comments`;
};

export const getKeyDataBySortType = (type: SortTypeValue) => {
  const mode = getValueSortTypeMode(type);

  return getKeyDataBySortTypeMode(mode);
};

export const getDataBySortType = (
  data: Record<string, any>,
  type: SortTypeValue
) => {
  if (!data) return;

  const key = getKeyDataBySortType(type);

  return data[key];
};

export const getInitDataBySortType = (
  data: Record<string, any>,
  type: SortTypeValue
) => {
  if (!data) return [];

  if (type === SORT_RELEVANT) return data['relevant_comments'] || [];

  return getDataBySortType(data, type) || [];
};

export const getListingDataBySortType = (
  padingData,
  data: Record<string, any>,
  type: SortTypeValue
) => {
  if (!data) return [];

  const { ids = [] } = padingData || {};
  const initData = getInitDataBySortType(data, type);

  return uniq([...initData, ...ids]);
};

export const getListingReplyDataBySortType = (
  padingData,
  data: Record<string, any>,
  type: SortTypeValue
) => {
  if (!data) return [];

  const { ids = [] } = padingData || {};
  const initData =
    (type === SORT_RELEVANT ? data['relevant_children'] : data['children']) ||
    [];

  return uniq([...initData, ...ids]);
};

export const getUpdateReplyContain = (data, type, count) => {
  if (type === SORT_MODE_ASC) {
    return { ...data, total_more_remaining: data.total_more_remaining - count };
  }

  return {
    ...data,
    total_previous_remaining: data.total_previous_remaining - count
  };
};

const userAgent = navigator.userAgent.toLowerCase();

export const isTabletMobileMode =
  // eslint-disable-next-line max-len
  /(ipad|tablet|(android(?!.*mobile))|(windows(?!.*phone)(.*touch))|kindle|playbook|silk|(puffin(?!.*(IP|AP|WP))))/.test(
    userAgent
  );

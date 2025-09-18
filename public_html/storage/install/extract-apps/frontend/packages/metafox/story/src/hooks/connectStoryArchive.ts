import { connect, GlobalState } from '@metafox/framework';
import { findIndex, get } from 'lodash';
import { prefixPagingId_archive } from '../constants';
import moment from 'moment';

export const shouldPreload = (total, position, nearly = 3) => {
  if (position < nearly) return 'prev';

  if (position > total - nearly - 1) return 'next';

  return '';
};

const mapStateToProps = (state: GlobalState, props: any) => {
  const { identity } = props;
  const item = get(state, identity);

  const url = new URL(window.location.href);
  const date = new URLSearchParams(url.search).get('date');

  if (!item) {
    return {};
  }

  const archiveDate = moment(date).format('L');

  const pagingId = `${prefixPagingId_archive}/${archiveDate}`;

  const stories = get(state, `pagination.${pagingId}.ids`);

  const total = get(state, `pagination.${pagingId}.pagesOffset.total`);
  const ended = get(state, `pagination.${pagingId}.ended`);
  const nextDate = get(state, `pagination.${pagingId}.pagesOffset.next_date`);
  const prevDate = get(state, `pagination.${pagingId}.pagesOffset.prev_date`);
  const current_page = ended
    ? 1
    : get(state, `pagination.${pagingId}.pagesOffset.current_page`);
  const per_page = get(state, `pagination.${pagingId}.pagesOffset.per_page`);

  const user = item?.user ? get(state, item.user) : undefined;
  const currentTotal = stories?.length;
  const pos =
    1 < currentTotal ? findIndex(stories, (x: string) => x === identity) : -1;

  const indexStoryActive = pos !== -1 ? pos : 0;

  const positionStory = ended
    ? indexStoryActive
    : get(state, `pagination.${pagingId}.pagesOffset.pos`) || 0;
  const indexStory = per_page * (current_page - 1) + positionStory;

  const result = {
    item,
    stories,
    user,
    total,
    nextDate,
    prevDate,
    pagingId,
    date,
    indexStoryActive,
    positionStory: indexStory,
    shouldPreload: shouldPreload(currentTotal, positionStory + 1)
  };

  return result;
};

export default connect(mapStateToProps);

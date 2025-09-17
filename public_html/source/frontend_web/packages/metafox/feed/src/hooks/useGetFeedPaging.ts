import { GlobalState } from '@metafox/framework/Manager';
import { useSelector } from 'react-redux';
import { AppState } from '@metafox/feed';

export default function useGetFeedPaging() {
  const data = useSelector<GlobalState, AppState['paging']>(
    state => state.feed.paging
  );

  return data;
}

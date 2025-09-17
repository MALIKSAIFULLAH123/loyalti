/**
 * @type: service
 * name: useAdmincpSiteLoading
 */
import { GlobalState } from '@metafox/framework';
import { useSelector } from 'react-redux';
import { createSelector } from 'reselect';

const getAdmincpSiteLoading = (state: GlobalState): boolean =>
  state?.admincp?.general?.loading;

const getAdmincpSiteLoadingSelector = createSelector(
  getAdmincpSiteLoading,
  loading => loading
);

export default function useAdmincpSiteLoading() {
  const loading = useSelector(getAdmincpSiteLoadingSelector);

  return loading;
}

/**
 * @type: service
 * name: useSubject
 */
import { getItemSelector, GlobalState, useGlobal } from '@metafox/framework';
import { useSelector } from 'react-redux';

export default function useSubject<T>(): T | undefined {
  const { usePageParams } = useGlobal();
  const { identity } = usePageParams();

  return useSelector<GlobalState, T>(state => getItemSelector(state, identity));
}

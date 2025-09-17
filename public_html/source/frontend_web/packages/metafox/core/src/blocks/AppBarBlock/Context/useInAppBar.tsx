/**
 * @type: service
 * name: useInAppBar
 */

import Context from './Context';
import { useContext } from 'react';

export default function useInAppBar(): [boolean] {
  const [inAppBar] = useContext(Context);

  return [inAppBar];
}

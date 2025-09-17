/**
 * @type: service
 * name: useScrollRef
 */
import React from 'react';
import ScrollContext from './ScrollContext';

function useScrollRef(): React.MutableRefObject<HTMLElement> {
  return React.useContext(ScrollContext);
}

export default useScrollRef;

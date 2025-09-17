/**
 * @type: service
 * name: ScrollProvider
 */
import React from 'react';
import ScrollContext from './ScrollContext';

export default function ScrollProvider({ children, scrollRef }) {
  return (
    <ScrollContext.Provider value={scrollRef}>
      {children}
    </ScrollContext.Provider>
  );
}

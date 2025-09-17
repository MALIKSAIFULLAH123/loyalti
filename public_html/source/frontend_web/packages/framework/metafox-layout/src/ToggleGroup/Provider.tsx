/**
 * @type: service
 * name: ToggleGroupProvider
 */
import ToggleContext from './Context';
import React from 'react';

function ToggleGroupProvider({ children }) {
  const state = React.useState<Record<string, string>>({});

  return (
    <ToggleContext.Provider value={state}>{children}</ToggleContext.Provider>
  );
}

export default ToggleGroupProvider;

import InAppBarContext from './Context';
import React from 'react';

function InAppBarProvider({ children }) {
  const state = React.useState<boolean>(true);

  return (
    <InAppBarContext.Provider value={state}>
      {children}
    </InAppBarContext.Provider>
  );
}

export default InAppBarProvider;

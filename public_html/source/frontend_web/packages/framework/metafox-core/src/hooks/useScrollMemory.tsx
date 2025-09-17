import React from 'react';

const useScrollMemory = () => {
  React.useEffect(() => {
    localStorage.setItem('positionY', window.pageYOffset.toString());
  }, []);

  return null;
};

export default useScrollMemory;

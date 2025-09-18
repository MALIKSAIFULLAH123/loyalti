import { useGlobal } from '@metafox/framework';
import React from 'react';

const BrowseSevents = props => {
  const { jsxBackend } = useGlobal();

  const ListView = jsxBackend.get('core.block.mainListing');
  
  return React.createElement(ListView, { ...props });
};

export default BrowseSevents;

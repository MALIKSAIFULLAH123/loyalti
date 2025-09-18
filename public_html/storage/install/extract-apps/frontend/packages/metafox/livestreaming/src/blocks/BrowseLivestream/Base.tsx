import { useGlobal } from '@metafox/framework';
import React from 'react';

const BrowseLivestreams = props => {
  const { jsxBackend } = useGlobal();

  const ListView = jsxBackend.get('core.block.listview');

  return React.createElement(ListView, { ...props });
};

export default BrowseLivestreams;

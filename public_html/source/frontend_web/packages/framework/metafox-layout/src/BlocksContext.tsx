import * as React from 'react';

type BlockItem = { component: string; props?: Record<string, any> };

const BlocksContext = React.createContext<BlockItem[]>([]);

export default BlocksContext;

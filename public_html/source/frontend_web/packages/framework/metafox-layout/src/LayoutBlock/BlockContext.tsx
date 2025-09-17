import { BlockViewProps } from '@metafox/framework';
import React from 'react';

const BlockContext = React.createContext<Partial<BlockViewProps>>({});

export default BlockContext;

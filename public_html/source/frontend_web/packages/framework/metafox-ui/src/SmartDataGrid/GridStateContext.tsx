import { GridDataState } from '@metafox/framework';
import { createContext } from 'react';

const GridDataStateContext = createContext<GridDataState>(undefined);

export default GridDataStateContext;

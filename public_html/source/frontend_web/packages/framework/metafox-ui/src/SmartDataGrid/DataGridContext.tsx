import { DataGridContextShape } from '@metafox/framework';
import { createContext } from 'react';

const DataGridContext = createContext<DataGridContextShape>(undefined);

export default DataGridContext;

import { useContext } from 'react';
import DataGridContext from './DataGridContext';

export default function useDataGridContext() {
  return useContext(DataGridContext);
}

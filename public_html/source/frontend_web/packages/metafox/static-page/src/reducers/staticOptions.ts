import produce from 'immer';
import { AppState } from '../types';

const data: AppState['staticOptions'] = [];

export default produce((draft, action) => {}, data);

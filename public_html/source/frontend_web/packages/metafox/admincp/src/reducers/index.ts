/**
 * @type: reducer
 * name: admincp
 */

import { combineReducers } from 'redux';
import status from './status';
import general from './general';

const reducers = combineReducers({
  status,
  general
});

export default reducers;

/**
 * @type: reducer
 * name: tourguide
 */

import { combineReducers, createEntityReducer } from '@metafox/framework';
import { APP_NAME } from '../constants';
import statusTourguide from './statusTourguide';

export default combineReducers({
  entities: createEntityReducer(APP_NAME),
  statusTourguide
});

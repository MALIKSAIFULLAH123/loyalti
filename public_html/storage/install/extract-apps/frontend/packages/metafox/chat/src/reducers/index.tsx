/**
 * @type: reducer
 * name: chat
 */

import { createEntityReducer } from '@metafox/framework';
import { combineReducers } from 'redux';
import resourceConfig from './resourceConfig';
import chatRooms from './chatRooms';
import openRooms from './openRooms';
import notifications from './notifications';

export default combineReducers({
  entities: createEntityReducer('chat'),
  resourceConfig,
  chatRooms,
  openRooms,
  notifications
});

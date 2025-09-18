/**
 * @type: reducer
 * name: chatplus
 */

import { createEntityReducer } from '@metafox/framework';
import { combineReducers } from 'redux';
import buddyPanel from './buddyPanel';
import calls from './calls';
import chatRooms from './chatRooms';
import friends from './friends';
import newChatRoom from './newChatRoom';
import openRooms from './openRooms';
import permissions from './permissions';
import resourceConfig from './resourceConfig';
import room from './room';
import roomFiles from './roomFiles';
import session from './session';
import settings from './settings';
import userPreferences from './userPreferences';
import users from './users';
import spotlight from './spotlight';
import notifications from './notifications';

export default combineReducers({
  entities: createEntityReducer('chatplus'),
  buddyPanel,
  resourceConfig,
  session,
  settings,
  userPreferences,
  permissions,
  users,
  friends,
  chatRooms,
  openRooms,
  newChatRoom,
  room,
  calls,
  roomFiles,
  spotlight,
  notifications
});

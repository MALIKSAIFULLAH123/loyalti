import { createResourceConfigReducer } from '@metafox/framework';
import message from './message';
import roomDockChat from './roomDockChat';
import roomItem from './roomItem';
import roomPageAll from './roomPageAll';
import msgFilterPopperMenu from './msgFilterPopperMenu';

export default createResourceConfigReducer('chatplus', {
  message,
  roomDockChat,
  roomPageAll,
  roomItem,
  msgFilterPopperMenu
});

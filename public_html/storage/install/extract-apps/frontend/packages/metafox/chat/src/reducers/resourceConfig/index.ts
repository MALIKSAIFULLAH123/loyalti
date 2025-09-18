import { createResourceConfigReducer } from '@metafox/framework';
import message from './message';
import roomDockChat from './roomDockChat';
import roomItem from './roomItem';
import roomPageAll from './roomPageAll';

export default createResourceConfigReducer('chat', {
  message,
  roomDockChat,
  roomPageAll,
  roomItem
});

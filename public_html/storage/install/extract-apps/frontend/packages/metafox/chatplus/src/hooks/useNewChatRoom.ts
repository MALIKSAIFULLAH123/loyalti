import { GlobalState } from '@metafox/framework';
import { useSelector } from 'react-redux';
import { getNewChatRoom } from '../selectors';
import { AppState } from '../types';

export default function useNewChatRoom() {
  return useSelector<GlobalState, AppState['newChatRoom']>(state =>
    getNewChatRoom(state)
  );
}

import { GlobalState } from '@metafox/framework';
import { useSelector } from 'react-redux';
import { getMessageItemSelector } from '../selectors';
import { MsgItemShape } from '../types';

export default function useMsgItem(identity: string): MsgItemShape | undefined {
  return useSelector<GlobalState, MsgItemShape>(state =>
    getMessageItemSelector(state, identity)
  );
}

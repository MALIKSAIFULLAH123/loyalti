import produce, { Draft } from 'immer';
import { AppState, InitResultShape } from '../types';

const init = {};

export default produce(
  (draft: Draft<AppState['friends']>, { type, payload }) => {
    switch (type) {
      case 'chatplus/init': {
        const friends: InitResultShape['friends'] = payload?.friends;

        if (!friends) return;

        friends.forEach(x => {
          draft[x._id] = x;
        });

        break;
      }

      // change status friends in list
      case 'chatplus/users/updateStatus': {
        const { _id, status, invisible, lastStatusUpdated } = payload;

        if (draft[_id]) {
          draft[_id] = { ...draft[_id], status, invisible, lastStatusUpdated };
        }

        break;
      }
      // change bio friends in list
      case 'chatplus/users/updateBio': {
        const { _id, bio } = payload;

        if (!draft[_id]) return;

        if (draft[_id]) {
          draft[_id] = { ...draft[_id], bio };
        }

        break;
      }
      // change avatarETag friends in list
      case 'chatplus/users/updateAvatar': {
        const { _id, avatarETag } = payload;

        if (!draft[_id]) return;

        if (draft[_id]) {
          draft[_id] = {
            ...draft[_id],
            avatarETag
          };
        }

        break;
      }

      case 'chatplus/onUserNameChanged': {
        const { _id, ...rest } = payload;

        if (!draft[_id]) return;

        if (draft[_id]) {
          draft[_id] = {
            ...draft[_id],
            ...rest
          };
        }

        break;
      }
      case 'chatplus/friends/add': {
        const friends: InitResultShape['friends'] = payload?.payload?.friends;

        if (!friends) return;

        friends.forEach(x => {
          if (!draft[x._id]) {
            draft[x._id] = x;
          }
        });

        break;
      }
      case 'chatplus/friends/removed': {
        const friends: InitResultShape['friends'] = payload?.payload?.friends;

        if (!friends) return;

        friends.forEach(x => {
          if (draft[x._id]) {
            delete draft[x._id];
          }
        });

        break;
      }

      default:
        return draft;
    }
  },
  init
);

import produce, { Draft } from 'immer';
import { AppState, InitResultShape } from '../types';

const init = {};

export default produce((draft: Draft<AppState['users']>, { type, payload }) => {
  switch (type) {
    case 'chatplus/init': {
      const users: InitResultShape['users'] = payload?.users;
      const session: InitResultShape['login'] = payload?.login;

      if (session?._id) {
        draft[session._id] = session;
      }

      if (!users) return;

      users.forEach(x => {
        draft[x._id] = x;
      });

      break;
    }
    case 'chatplus/users/add': {
      const { _id } = payload;

      if (!draft[_id]) {
        draft[_id] = payload;
      }

      break;
    }
    case 'chatplus/users/removed': {
      const { _id } = payload;

      if (draft[_id]) {
        delete draft[_id];
      }

      break;
    }
    case 'chatplus/users/updateStatus': {
      const { _id, status, invisible, lastStatusUpdated } = payload;

      if (draft[_id]) {
        draft[_id] = { ...draft[_id], status, invisible, lastStatusUpdated };
      }

      break;
    }
    // change bio
    case 'chatplus/users/updateBio': {
      const { _id, bio } = payload;

      if (!draft[_id]) return;

      if (draft[_id]) {
        draft[_id] = { ...draft[_id], bio };
      }

      break;
    }
    // change avatar
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
    default:
      return draft;
  }
}, init);

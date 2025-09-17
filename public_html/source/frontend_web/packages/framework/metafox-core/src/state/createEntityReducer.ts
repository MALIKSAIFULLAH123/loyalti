/* eslint-disable no-loop-func */
import produce, { Draft } from 'immer';
import { assign, get, merge, set, unset } from 'lodash';
import {
  ENTITY_DELETE,
  ENTITY_FULFILL,
  ENTITY_PATCH,
  ENTITY_PUT,
  LOGGED_OUT,
  PAGINATION_SUCCESS
} from '../constants';

type EntityPayload = Record<string, Record<string, any>>;

function isObject(obj) {
  return obj && typeof obj === 'object' && !Array.isArray(obj) && obj !== null;
}

export default function createEntityReducer(appName: string) {
  const prefix = `${appName}.entities.`;

  return produce((draft: Draft<EntityPayload>, { type, payload }) => {
    switch (type) {
      case PAGINATION_SUCCESS:
      case ENTITY_FULFILL: {
        if (
          !payload ||
          !payload.data ||
          !payload ||
          !payload.data[appName] ||
          !payload.data[appName].entities
        )
          return;

        const entities = payload.data[appName].entities;

        for (const key of Object.keys(entities)) {
          if (!draft[key]) {
            draft[key] = {};
          }

          Object.keys(entities[key]).forEach(id => {
            // reset error when update data
            if (get(draft[key][id], 'error')) {
              draft[key][id]['error'] = undefined;
            }

            // merge deep extra data
            if (
              isObject(get(draft[key][id], 'extra')) &&
              isObject(get(entities[key][id], 'extra'))
            ) {
              draft[key][id] = assign({}, draft[key][id], {
                ...entities[key][id],
                extra: {
                  ...draft[key][id]['extra'],
                  ...entities[key][id]['extra']
                }
              });
            } else {
              draft[key][id] = assign({}, draft[key][id], entities[key][id]);
            }
          });
        }

        return draft;
      }
      case ENTITY_PUT: {
        if (
          !payload.identity ||
          !payload.data ||
          !payload.identity.startsWith(prefix)
        ) {
          return;
        }

        const path = payload.identity.replace(prefix, '');

        set(draft, path, payload.data);

        return draft;
      }
      case ENTITY_DELETE: {
        if (!payload.identity || !payload.identity.startsWith(prefix)) {
          return;
        }

        const path = payload.identity.replace(prefix, '');

        if (!path) return;

        unset(draft, path);
        break;
      }
      case ENTITY_PATCH: {
        if (
          !payload.identity ||
          !payload.data ||
          !payload.identity.startsWith(prefix)
        ) {
          return;
        }

        const path = payload.identity.replace(prefix, '');

        if (!path) return;

        const exists = get(draft, path);

        if (!exists) {
          return;
        }

        const newValue = !payload.deepMerge
          ? Object.assign(exists, payload.data)
          : merge(exists, payload.data);
        set(draft, path, newValue);

        return draft;
      }
      case LOGGED_OUT:
        return {};
    }
  }, {});
}

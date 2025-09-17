import { AppResourceConfig } from '@metafox/framework';
import produce from 'immer';
import { get } from 'lodash';
import { ACTION_LOAD_SETTING } from './constants';

export default function createResourceConfigReducer(
  appName: string,
  initialState?: AppResourceConfig
) {
  return produce((draft, action) => {
    switch (action.type) {
      case ACTION_LOAD_SETTING: {
        const resources = get(action.payload, `${appName}.setting.resources`);

        if (resources) {
          Object.keys(resources).forEach(key => {
            draft[key] = resources[key];
          });
        }

        break;
      }
    }
  }, initialState || {});
}

import { AppUIConfig } from '@metafox/framework';
import produce from 'immer';
import { get } from 'lodash';
import { ACTION_LOAD_SETTING } from './constants';

export default function createUIReducer(
  appName: string,
  initialState: AppUIConfig
) {
  return produce((draft, action) => {
    switch (action.type) {
      case ACTION_LOAD_SETTING: {
        const menus = get(action.payload, '_menus');

        if (menus) draft.menus = menus;

        break;
      }
    }
  }, initialState);
}

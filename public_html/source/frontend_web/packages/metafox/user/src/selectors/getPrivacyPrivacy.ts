import { GlobalState } from '@metafox/framework';
import { get } from 'lodash';

export const getAllPrivacySelector = (appState: GlobalState) => {
  const sharingSetting = get(appState, 'user.sharingItemPrivacy.data');

  return sharingSetting;
};

export const getPrivacyPrivacySelector = (
  appState: GlobalState,
  module_id: string
) => {
  if (!module_id) return;

  const sharingSetting = get(appState, 'user.sharingItemPrivacy.data');

  return sharingSetting.find(
    item => item.var_name === `${module_id}:item_privacy`
  );
};

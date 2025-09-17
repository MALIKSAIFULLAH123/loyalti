/**
 * @type: service
 * name: usePreference
 */
import { UserPreferenceConfig, UserPreferenceContext } from '@metafox/framework';
import React from 'react';

export default function usePreference(): UserPreferenceConfig {
  return React.useContext(UserPreferenceContext);
}

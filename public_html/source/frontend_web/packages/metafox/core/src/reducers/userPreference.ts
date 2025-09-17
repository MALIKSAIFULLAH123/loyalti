/**
 * @type: reducer
 * name: userPreference
 */
import { createReducer } from '@reduxjs/toolkit';
import { MFOX_LOCALE, UserPreferenceConfig } from '@metafox/framework';

export default createReducer<UserPreferenceConfig>(
  {
    layoutPreviewWindow: false,
    userLanguage: MFOX_LOCALE,
    userDirection: 'ltr',
    themeId: 'default',
    themeType: 'light'
  },
  builder => {}
);

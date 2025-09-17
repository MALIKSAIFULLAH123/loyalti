/**
 * @type: reducer
 * name: privacy
 */
import { combineReducers, createUIReducer } from '@metafox/framework';
import privacyOptions from './privacyOptions';

const appName = 'privacy';
export default combineReducers({
  privacyOptions,
  uiConfig: createUIReducer(appName, {})
});

/**
 * @type: reducer
 * name: story
 */

import {
  combineReducers,
  createEntityReducer,
  createUIReducer
} from '@metafox/framework';
import uiConfig from './uiConfig';
import storyStatus from './storyStatus';

const appName = 'story';

export default combineReducers({
  entities: createEntityReducer(appName),
  uiConfig: createUIReducer(appName, uiConfig),
  storyStatus
});

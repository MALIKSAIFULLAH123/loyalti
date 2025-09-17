/**
 * @type: reducer
 * name: feed
 */

import {
  combineReducers,
  createEntityReducer,
  createUIReducer
} from '@metafox/framework';
import uiConfig from './uiConfig';
import pagingData from './pagingData';

const appName = 'feed';

export default combineReducers({
  entities: createEntityReducer(appName),
  uiConfig: createUIReducer(appName, uiConfig),
  paging: pagingData
});

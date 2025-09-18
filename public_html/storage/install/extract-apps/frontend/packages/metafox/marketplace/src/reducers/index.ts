/**
 * @type: reducer
 * name: marketplace
 */
import {
  combineReducers,
  createEntityReducer,
  createUIReducer
} from '@metafox/framework';
import uiConfig from './uiConfig';
import marketplaceActive from './active';

const appName = 'marketplace';

export default combineReducers({
  entities: createEntityReducer(appName),
  uiConfig: createUIReducer(appName, uiConfig),
  marketplaceActive
});

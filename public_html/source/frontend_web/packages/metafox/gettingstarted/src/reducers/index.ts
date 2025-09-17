/**
 * @type: reducer
 * name: getting-started
 */
import {
  combineReducers,
  createEntityReducer,
  createUIReducer
} from '@metafox/framework';
import uiConfig from './uiConfig';
import stepListing from './stepListing';

const appName = 'getting-started';

const reducers = combineReducers({
  entities: createEntityReducer(appName),
  uiConfig: createUIReducer(appName, uiConfig),
  stepListing
});

export default reducers;

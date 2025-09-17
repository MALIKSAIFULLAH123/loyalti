/**
 * @type: reducer
 * name: core
 */

import { createEntityReducer, createUIReducer } from '@metafox/framework';
import { combineReducers } from 'redux';
import status from './status';
import uiConfig from './uiConfig';

const appName = 'core';

const reducers = combineReducers({
  entities: createEntityReducer(appName),
  status,
  uiConfig: createUIReducer(appName, uiConfig)
});

export default reducers;

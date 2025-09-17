import { combineReducers } from 'redux';
import { AppUIConfig } from '../types';
import createEntityReducer from './createEntityReducer';
import createUIReducer from './createUIReducer';

export default function createAppReducer(
  appName: string,
  uiConfig?: AppUIConfig
) {
  return combineReducers({
    uiConfig: createUIReducer(appName, uiConfig),
    entities: createEntityReducer(appName)
  });
}

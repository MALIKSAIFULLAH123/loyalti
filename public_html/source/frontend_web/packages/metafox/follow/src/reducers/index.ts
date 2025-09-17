/**
 * @type: reducer
 * name: follow
 */
import { combineReducers, createEntityReducer } from '@metafox/framework';
import { APP_FOLLOW } from '../constants';

const reducers = combineReducers({
  entities: createEntityReducer(APP_FOLLOW)
});

export default reducers;

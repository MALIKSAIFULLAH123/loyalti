/**
 * Create the store with dynamic reducers
 */
import { configureStore } from '@reduxjs/toolkit';
import { createInjectorsEnhancer, forceReducerReload } from 'redux-injectors';
import createSagaMiddleware from 'redux-saga';
import { all } from 'redux-saga/effects';
import createReducer from './createReducer';

const devTools = true;

export interface ConfigureStoreParams {
  preloadedState?: Record<string, any>;
  rootSagas?: any;
  rootReducers?: any;
  globalContext?: Record<string, any>;
}

export default function createStore({
  preloadedState = {},
  rootSagas = [],
  rootReducers = {},
  globalContext = {}
}: ConfigureStoreParams = {}) {
  const sagaMiddleware = createSagaMiddleware({
    context: {
      useGlobal: globalContext
    }
  });

  const { run: runSaga } = sagaMiddleware;
  // Create the store with saga middleware
  const middleware = [sagaMiddleware];

  const myEnhancer = [
    createInjectorsEnhancer({
      createReducer,
      runSaga
    })
  ].filter(Boolean);

  function* rootSaga() {
    try {
      yield all(rootSagas.flat());
    } catch (error) {
      // eslint-disable-next-line
      console.error(error);
    }
  }

  const store = configureStore({
    preloadedState,
    reducer: createReducer(rootReducers),
    middleware: getDefaultMiddleware =>
      getDefaultMiddleware({
        serializableCheck: false,
        immutableCheck: false,
        thunk: false
      }).concat(middleware),
    devTools,
    enhancers: getDefaultEnhancers => {
      return getDefaultEnhancers().concat(myEnhancer);
    }
  });

  globalContext.dispatch = store.dispatch;
  globalContext.getState = store.getState;

  sagaMiddleware.run(rootSaga);

  // Make reducers hot reload, see http://mxs.is/googmo
  /* istanbul ignore next */
  if (module.hot) {
    module.hot.accept('./createReducer', () => {
      forceReducerReload(store);
    });
  }

  return store;
}

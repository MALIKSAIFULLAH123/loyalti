import { configureStore } from './index';
import { put, takeEvery } from 'redux-saga/effects';

function* testSaga01() {
  yield put({
    type: 'action/testSaga02',
    payload: 'put from testSaga01'
  });
}

function* testSaga02() {
  yield put({
    type: 'action/testSaga03',
    payload: 'put from testSaga02'
  });
}

const rootSagas = [
  takeEvery('action/testSaga01', testSaga01),
  takeEvery('action/testSaga02', testSaga02)
];

describe('configureStore', () => {
  describe('preloadedState', () => {
    let store;
    beforeAll(() => {
      const rootReducers = {
        start: (prev = 1, action) => {
          if ('start/changed' === action.type) {
            return action.payload;
          }

          return prev;
        }
      };

      store = configureStore({
        preloadedState: { start: 3 },
        rootSagas: [],
        rootReducers
      });
    });
    it('configureStore with initialized', () => {
      expect(store).toEqual(
        expect.objectContaining({
          runSaga: expect.any(Function),
          injectedReducers: expect.any(Object),
          injectedSagas: expect.any(Object)
        })
      );
      expect(store.getState()).toBeDefined();
      expect(store.getState().start).toEqual(3);
      store.dispatch({ type: 'start/changed', payload: 2 });
      expect(store.getState().start).toEqual(2);
    });
  });
  describe('rootSagas', () => {
    let store;
    beforeAll(() => {
      const rootReducers = {
        sagaResult: (prev = 0, action) => {
          switch (action.type) {
            case 'action/testSaga03':
              return action.payload;
            default:
              return prev;
          }
        }
      };

      store = configureStore({
        rootReducers,
        rootSagas
      });
    });
    it('configureStore with initialized', () => {
      expect(store.getState()).toBeDefined();
      expect(store.getState().sagaResult).toEqual(0);
      expect(store.dispatch({ type: 'action/testSaga01', payload: undefined }));
      expect(store.getState().sagaResult).toEqual('put from testSaga02');
    });
  });
});

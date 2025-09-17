/**
 * @type: saga
 * name: core.actionMenuSaga
 */
import {
  APP_BOOTSTRAP,
  CLOSE_MENU,
  getGlobalContext,
  LocalAction
} from '@metafox/framework';
import { isFunction } from 'lodash';
import { takeEvery, takeLeading } from 'redux-saga/effects';

type Action = LocalAction<
  unknown,
  { closeMenu: () => void; setLocalState: (cb: any) => void }
>;

// eslint-disable-next-line @typescript-eslint/no-unused-vars
function* handleToggleMenu({ meta }: Action) {
  if (isFunction(meta.setLocalState)) {
    // control menu via local state
    yield meta.setLocalState(prev => {
      return { ...prev, menuOpened: !prev.menuOpened };
    });
  }
}

function* handleCloseMenu({ meta }: Action) {
  // self control menu
  if (isFunction(meta?.closeMenu)) {
    yield meta.closeMenu();
  } else if (isFunction(meta?.setLocalState)) {
    // control menu via local state
    meta.setLocalState(prev => {
      return { ...prev, menuOpened: !prev.menuOpened };
    });
  }
}

function* skipResources() {
  const { normalization } = yield* getGlobalContext();

  // etc 'blog_category'
  normalization.skipResources([]);
}

const sagas = [
  // takeEvery('toggleMenu', handleToggleMenu),
  takeEvery(CLOSE_MENU, handleCloseMenu),
  takeLeading(APP_BOOTSTRAP, skipResources)
];

export default sagas;

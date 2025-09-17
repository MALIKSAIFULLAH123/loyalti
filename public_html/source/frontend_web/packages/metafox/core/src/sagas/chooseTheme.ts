/**
 * @type: saga
 * name: core.chooseTheme
 */

import { getGlobalContext } from '@metafox/framework';
import { takeLatest } from 'redux-saga/effects';

function* chooseTheme() {
  const { dialogBackend, preferenceBackend, dispatch } =
    yield* getGlobalContext();

  const themeId = yield dialogBackend.present({
    component: 'core.dialog.ChooseTheme',
    props: {}
  });

  if (themeId) {
    preferenceBackend.setTheme(themeId, () => {
      dispatch({ type: 'navigate/reload' });
    });
  }
}

const sagas = [takeLatest('chooseTheme', chooseTheme)];

export default sagas;

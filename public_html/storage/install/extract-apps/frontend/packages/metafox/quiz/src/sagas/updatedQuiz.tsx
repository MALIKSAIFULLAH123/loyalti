/**
 * @type: saga
 * name: updatedQuiz
 */

import { LocalAction, viewItem } from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';

function* updateQuiz({ payload: { id } }: LocalAction<{ id: string }>) {
  yield* viewItem('quiz', 'quiz', id);
}

const sagas = [takeEvery('@updatedItem/quiz', updateQuiz)];

export default sagas;

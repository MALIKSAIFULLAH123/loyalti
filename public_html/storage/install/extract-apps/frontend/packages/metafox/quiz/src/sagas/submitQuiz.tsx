/**
 * @type: saga
 * name: quiz.saga.submitQuiz
 */

import {
  getGlobalContext,
  handleActionError,
  handleActionFeedback,
  fulfillEntity
} from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';

type SubmitQuizAction = {
  type: string;
  payload: {
    quiz_id: string;
    answers: Record<string, number>;
    result: string;
  };
  meta?: {
    onSuccess: () => void;
  };
};

function* submitQuiz(action: SubmitQuizAction) {
  const {
    payload: { quiz_id, answers },
    meta: { onSuccess, onError }
  } = action;
  const { apiClient, normalization } = yield* getGlobalContext();

  try {
    const response = yield apiClient.request({
      method: 'post',
      url: '/quiz-result',
      data: {
        quiz_id,
        answers
      }
    });
    const data = response?.data?.data;

    if (onSuccess) onSuccess();

    if (data) {
      const result = normalization.normalize(data);

      yield* fulfillEntity(result.data);

      yield* handleActionFeedback(response);
    }
  } catch (error) {
    if (onError) onError();

    yield* handleActionError(error);
  }
}

const sagas = [takeEvery('quiz/submitQuiz', submitQuiz)];

export default sagas;

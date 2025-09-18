/**
 * @type: saga
 * name: poll.saga.submitPoll
 */

import {
  getGlobalContext,
  handleActionError,
  handleActionFeedback,
  patchEntity,
  PAGINATION_CLEAR
} from '@metafox/framework';
import { Method } from 'axios';
import { isFunction } from 'lodash';
import { all, call, takeEvery } from 'redux-saga/effects';

type SubmitPollAction = {
  type: string;
  payload: {
    voteAgain: boolean;
    pollId: string;
    answers: string[] | string;
    identity: string;
  };
  meta?: {
    onSuccess: () => void;
  };
};

type Request = {
  method: Method;
  url: string;
  data?: any;
};

function* updateAnswerPoll(item) {
  try {
    yield* patchEntity(`poll.entities.poll_answer.${item.id}`, item);
    // eslint-disable-next-line no-empty
  } catch (error) {}
}

function* submitPoll(action: SubmitPollAction) {
  const {
    payload: { voteAgain, pollId, answers, identity },
    meta
  } = action;
  const { apiClient, dispatch } = yield* getGlobalContext();
  const request: Request = voteAgain
    ? { method: 'put', url: `/poll-result/${pollId}`, data: { answers } }
    : {
        method: 'post',
        url: '/poll-result',
        data: {
          poll_id: pollId,
          answers
        }
      };

  try {
    const response = yield apiClient.request(request);

    const { answers, statistic, extra, is_user_voted } = response?.data?.data;

    yield all(answers.map(item => call(updateAnswerPoll, item)));

    answers.forEach(item => {
      call(updateAnswerPoll, item);
      dispatch({
        type: PAGINATION_CLEAR,
        payload: { pagingId: `pollAnswerResult/${item.id}` }
      });
    });

    yield* patchEntity(identity, {
      statistic,
      extra,
      is_user_voted
    });
    yield* handleActionFeedback(response);
    isFunction(meta.onSuccess) && meta.onSuccess(extra);
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [takeEvery('submitPoll', submitPoll)];

export default sagas;

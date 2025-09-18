/**
 * @type: saga
 * name: livestreaming.saga.updateComment
 */

import {
  ItemLocalAction,
  getGlobalContext,
  fulfillEntity,
  getSession
} from '@metafox/framework';
import { pick, cloneDeep, get } from 'lodash';
import { takeLatest } from 'redux-saga/effects';

function* updateComment(
  action: ItemLocalAction & {
    payload: { data: Array<any> };
  }
) {
  const { data } = action.payload;
  const { normalization } = yield* getGlobalContext();
  const { user: authUser } = yield* getSession();

  try {
    if (data) {
      const result = normalization.normalize(cloneDeep(data));
      const dataEntities = pick(result.data, ['comment', 'user']);

      if (dataEntities?.comment) {
        const dataComment = get(dataEntities, 'comment.entities.comment');

        Object.values(dataComment).map(comment => {
          const live_user_reacted_data = comment?.lv_user_reacted?.find(
            x => x.user_id === authUser?.id
          );
          const live_user_reacted = live_user_reacted_data
            ? `preaction.entities.preaction.${live_user_reacted_data?.reaction_id}`
            : '';

          dataEntities.comment.entities.comment[comment.id].user_reacted =
            live_user_reacted;
        });
      }

      yield* fulfillEntity(dataEntities);
    }
  } catch (error) {}
}

const sagas = [takeLatest('livestreaming/updateComment', updateComment)];

export default sagas;

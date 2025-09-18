/**
 * @type: saga
 * name: reaction.reactionItem
 */

import {
  getGlobalContext,
  getItem,
  handleActionError,
  LocalAction,
  patchEntity
} from '@metafox/framework';
import { takeLatest } from 'redux-saga/effects';
import { LIMIT_SHOW_REACTION } from '../constants';
import {
  addOrUpdateItem,
  getIdentityFromId,
  getIdFormIdentity,
  moveOldOrPushItem
} from '../utils';
import { ItemReactionInformationShape } from '@metafox/ui';

type ReactionResult = {
  feed_id: number;
  most_reactions_information?: ItemReactionInformationShape[];
  user_reacted: any;
  is_liked: boolean;
  total_like: number;
};

function* updateItem(identity: string, result: ReactionResult) {
  const item = yield* getItem(identity);
  const user_reacted = result.user_reacted
    ? getIdentityFromId(result.user_reacted.id)
    : undefined;
  const total_like = result.total_like;
  const statistic = item.statistic
    ? {
        ...item.statistic,
        total_like
      }
    : { total_like };

  yield* patchEntity(identity, {
    statistic,
    is_liked: result.is_liked,
    most_reactions_information: result.most_reactions_information,
    user_reacted
  });
}

function* updateFeed(result: ReactionResult) {
  const identity = `feed.entities.feed.${result.feed_id}`;
  const item = yield* getItem(identity);

  if (!item) return;

  const user_reacted = result.user_reacted
    ? getIdentityFromId(result.user_reacted.id)
    : undefined;
  const total_like = result.total_like;
  const statistic = item.statistic
    ? { ...item.statistic, total_like }
    : { total_like };

  yield* patchEntity(identity, {
    statistic,
    is_liked: result.is_liked,
    most_reactions_information: result.most_reactions_information,
    user_reacted
  });
}

function* reactionItem(
  action: LocalAction<{
    identity: string;
    reaction_id: number;
  }>
) {
  const { apiClient } = yield* getGlobalContext();
  const { identity, reaction_id: reactionIdPayload } = action.payload;
  const reaction_id = Number(reactionIdPayload);
  const item = yield* getItem(identity);

  const { like_type_id, item_type, like_item_id, resource_name, id, item_id } =
    item;

  const idUserReacted = getIdFormIdentity(item.user_reacted);
  const identityReacted = getIdentityFromId(reaction_id);
  const total_like = item.statistic?.total_like ?? 0;
  const beRemoved = item.user_reacted === identityReacted && item.is_liked;
  const beAdded = !item.is_liked;
  const isChangeReacted =
    item.user_reacted && item.user_reacted !== identityReacted;
  const newValue = {
    is_liked: beRemoved ? false : true,
    statistic: { ...item.statistic },
    user_reacted: beRemoved ? undefined : identityReacted,
    most_reactions_information: item?.most_reactions_information
  };

  const itemReacted = (item?.most_reactions_information || []).find(
    item => item?.id === reaction_id
  );

  if (beAdded) {
    newValue.statistic.total_like = total_like + 1;

    if (item?.most_reactions_information?.length < LIMIT_SHOW_REACTION) {
      newValue.most_reactions_information = addOrUpdateItem(
        newValue.most_reactions_information,
        reaction_id
      );
    }
  }

  if (beRemoved) {
    newValue.statistic.total_like = total_like - 1;

    if (itemReacted?.total_reaction === 1) {
      newValue.most_reactions_information =
        newValue.most_reactions_information.filter(
          (item: { id: number }) => item?.id !== reaction_id
        );
    }
  }

  if (isChangeReacted) {
    newValue.most_reactions_information = moveOldOrPushItem(
      newValue.most_reactions_information,
      reaction_id,
      idUserReacted
    );
  }

  yield* patchEntity(identity, newValue);

  const sendData = {
    item_id: like_item_id || item_id || id,
    item_type: like_type_id || item_type || resource_name
  };

  try {
    const response = beRemoved
      ? yield apiClient.request({
          url: '/like',
          method: 'DELETE',
          params: sendData
        })
      : yield apiClient.request({
          url: '/like',
          method: 'POST',
          data: {
            ...sendData,
            reaction_id
          }
        });
    const result = response.data.data;
    yield* updateItem(identity, result);

    if (result.feed_id) {
      yield* updateFeed(result);
    }
  } catch (err) {
    const updateValue = {
      is_liked: item?.is_liked,
      statistic: { ...item.statistic },
      user_reacted: item?.user_reacted,
      most_reactions_information: item.most_reactions_information || []
    };

    yield* patchEntity(identity, updateValue);
    yield* handleActionError(err);
  }
}

const sagas = [takeLatest('reactionItem', reactionItem)];

export default sagas;

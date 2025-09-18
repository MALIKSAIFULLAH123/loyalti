import { GlobalState } from '@metafox/framework';
import { get } from 'lodash';
import { createSelector } from 'reselect';

const getCommentAttachment = (
  state: GlobalState,
  attachment_identity: string
) => {
  return get(state, attachment_identity);
};

export const getCommentAttachmentSelector = createSelector(
  getCommentAttachment,
  item => item
);

const getCommentExcept = (state: GlobalState, identity) => {
  return Object.values(state.comment.entities.comment || {})
    .filter(item => item._identity !== identity)
    .map(item => item._identity);
};

export const getCommentExceptIdentitySelector = createSelector(
  getCommentExcept,
  item => item
);

import { PagesItemAction } from '..';

export const mappingRelationship = (
  is_owner: boolean,
  isLiked: boolean,
  canUnlike: boolean,
  canLike: boolean,
  actions: PagesItemAction,
  isShared?: boolean
) => {
  if (
    typeof isLiked !== 'boolean' ||
    typeof canUnlike !== 'boolean' ||
    typeof canLike !== 'boolean' ||
    typeof is_owner !== 'boolean'
  )
    return;

  const disabled =
    (!(isLiked && canUnlike) && !(!isLiked && canLike) && is_owner) ||
    (!canLike && !canUnlike) ||
    isShared;
  const detail = {
    icon: isLiked ? 'ico-thumbup' : 'ico-thumbup-o',
    textId: isLiked ? 'liked' : 'like',
    variant: 'outlined-square',
    disabled,
    actions: isLiked ? actions.unlikePage : actions.likePage
  };

  return detail;
};

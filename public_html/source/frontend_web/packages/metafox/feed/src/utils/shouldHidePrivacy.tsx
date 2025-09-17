import { UserItemShape } from '@metafox/user';

const APP_GROUP = 'group';
const APP_PAGE = 'page';
const APP_EVENT = 'event';

export const shouldHidePrivacy = (
  parentIdentity: string,
  parentType: string,
  currentUser: UserItemShape
) => {
  if (!parentIdentity || !parentType || !currentUser) return false;

  const hidePrivacy =
    parentIdentity &&
    ([APP_GROUP, APP_PAGE, APP_EVENT].includes(parentType) ||
      (parentType === 'user' &&
        parentIdentity !== `user.entities.user.${currentUser.id}`));

  return hidePrivacy;
};

const APP_GROUP = 'group';
const APP_EVENT = 'event';

export const shouldShowTypePrivacy = (
  parentIdentity: string,
  parentType: string
) => {
  if (!parentIdentity || !parentType) return false;

  const shouldShowTypePrivacy =
    parentIdentity && [APP_GROUP, APP_EVENT].includes(parentType);

  return shouldShowTypePrivacy;
};

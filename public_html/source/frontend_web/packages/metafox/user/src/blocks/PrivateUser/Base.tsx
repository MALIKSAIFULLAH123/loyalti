import { useGlobal } from '@metafox/framework';
import { UserItemShape } from '@metafox/user';
import React from 'react';

export type Props = {
  item: UserItemShape;
};

const PrivateAccount = ({ item }: Props) => {
  const { jsxBackend, i18n } = useGlobal();

  if (!item?.profile_settings || item?.profile_settings?.profile_view_profile)
    return null;

  const NoResultsBlock = jsxBackend.get(
    'core.block.no_content_with_description'
  );

  return (
    <NoResultsBlock
      title={i18n.formatMessage({ id: 'profile_private' })}
      description={i18n.formatMessage({ id: 'profile_private_description' })}
    />
  );
};

export default PrivateAccount;

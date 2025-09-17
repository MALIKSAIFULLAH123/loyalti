import { useGlobal } from '@metafox/framework';
import { Tooltip, Box } from '@mui/material';
import React from 'react';
import LineIcon from './LineIcon';

export type PrivacyIconProps = {
  item?: Record<string, any>;
  value?: number;
  privacyText?: boolean;
};

enum Privacy {
  Everyone = 0,
  Friends = 2,
  Friends_of_friends = 3,
  Only_me = 4,
  Custom = 10,
  Community = 1
}

const privacyOption = (value: number): [string, string] => {
  switch (value) {
    case Privacy.Everyone:
      return ['public', 'ico-globe-o'];
    case Privacy.Community:
      return ['community', 'ico-user-circle'];
    case Privacy.Friends:
      return ['friends', 'ico-user-two-men'];
    case Privacy.Friends_of_friends:
      return ['friends_of_friends', 'ico-user-man-three'];
    case Privacy.Only_me:
      return ['only_me', 'ico-lock'];
    default:
      return ['custom', 'ico-gear'];
  }
};

export default function PrivacyIcon({
  item,
  value,
  privacyText = false
}: PrivacyIconProps) {
  const { i18n, getSetting } = useGlobal();
  const [id, iconStatic] = privacyOption(value);
  const title = item?.tooltip ? item?.tooltip : i18n.formatMessage({ id });
  const icon = getSetting(`platform.privacy_icon.${item?.privacy_icon}`);

  return (
    <Box component="span" sx={{ display: 'inline-flex', alignItems: 'center' }}>
      <Tooltip title={title}>
        <LineIcon
          icon={icon || iconStatic}
          aria-label={item?.tooltip}
          role="img"
          data-testid="iconPrivacy"
        />
      </Tooltip>
      {privacyText && `${item?.label || title}`}
    </Box>
  );
}

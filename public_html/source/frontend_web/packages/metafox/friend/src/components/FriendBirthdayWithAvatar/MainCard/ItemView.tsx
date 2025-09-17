import { useGlobal } from '@metafox/framework';
import { FriendItemProps } from '@metafox/friend/types';
import { useBlock } from '@metafox/layout';
import { ItemMedia, UserAvatar } from '@metafox/ui';
import { Box, styled } from '@mui/material';
import Tooltip, { TooltipProps, tooltipClasses } from '@mui/material/Tooltip';
import * as React from 'react';

const LightTooltip = styled(({ className, ...props }: TooltipProps) => (
  <Tooltip {...props} classes={{ popper: className }} />
))(({ theme }) => ({
  marginTop: `-${theme.spacing(2)}!important`,
  [`& .${tooltipClasses.arrow}`]: {
    display: 'none'
  }
}));

export default function FriendItem({
  item,
  user,
  identity,
  handleAction,
  state,
  wrapAs,
  wrapProps,
  actions
}: FriendItemProps) {
  const { i18n, moment } = useGlobal();
  const { itemProps: { media } = {} } = useBlock();

  const { birthday, age, birthday_format } = item || {};

  const day_phrase = moment(birthday, birthday_format).format(
    age ? 'LL' : 'MMMM D'
  );

  if (!item) return null;

  return (
    <LightTooltip
      title={i18n.formatMessage(
        { id: 'user_birthday_is_date' },
        {
          full_name: item.full_name,
          value: birthday ? day_phrase : null
        }
      )}
      placement="bottom"
    >
      <Box p={1}>
        <ItemMedia>
          <UserAvatar
            user={item}
            size={Number.parseInt(media?.width)}
            hoverCard={false}
          />
        </ItemMedia>
      </Box>
    </LightTooltip>
  );
}

FriendItem.displayName = 'FriendItemViewMainCard';

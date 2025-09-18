import { Link, useGlobal } from '@metafox/framework';
import {
  ItemMedia,
  ItemText,
  FormatDate,
  ItemTitle,
  ItemView,
  UserAvatar,
  FeaturedIcon
} from '@metafox/ui';
import * as React from 'react';
import { Box } from '@mui/material';

export default function UserItem({
  item,
  identity,
  wrapAs,
  wrapProps
}) {
  const {
    i18n,
    useTheme
  } = useGlobal();

  const theme = useTheme();

  if (!item) return null;

  const {
    full_name,
    user_name,
    is_featured,
    id
  } = item;
  const to = `/${user_name}`;
  const aliasPath = id ? `/user/${id}` : '';

  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={identity}
      style={{ marginBottom: '12px' }}
    >
      <Box gap='18px' display='flex' alignItems='center'>
        <ItemMedia>
          <UserAvatar user={item} size={64} hoverCard={`/user/${item.id}`} />
        </ItemMedia>
        <ItemText>
          <ItemTitle>
            <Box sx={{ display: 'flex', alignItems: 'center', maxWidth: '100%' }}>
              <Link
                sx={{ overflow: 'hidden', textOverflow: 'ellipsis' }}
                to={to}
                aliasPath={aliasPath}
                children={full_name}
                color={'inherit'}
                hoverCard={`/user/${item.id}`}
              />
              <FeaturedIcon icon="ico-check-circle" value={is_featured} />
            </Box>
          </ItemTitle>
          <div style={{ marginTop: '8px', color: theme.palette.text.secondary }}>
                {i18n.formatMessage({ id: 'sevent_joined' })}:&nbsp;
                <FormatDate
                  value={item.attend_date}
                  format="ll"
                />
          </div>
        </ItemText>
      </Box>
    </ItemView>
  );
}

UserItem.displayName = 'UserItemMainCard';

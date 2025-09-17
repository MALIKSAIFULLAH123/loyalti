import { useGlobal, useResourceMenu } from '@metafox/framework';
import {
  ItemActionMenu,
  ItemMedia,
  ItemText,
  ItemTitle,
  LineIcon,
  ItemView,
  UserAvatar
} from '@metafox/ui';
import { filterShowWhen } from '@metafox/utils';
import { styled, Typography } from '@mui/material';
import React from 'react';

const TypographyStyled = styled(Typography)(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(13),
  color: theme.palette.text.secondary,
  display: 'inline-flex',
  alignItems: 'center',
  cursor: 'pointer',
  '& .ico': {
    paddingLeft: theme.spacing(0.5),
    color: theme.palette.text.secondary
  }
}));

export default function ShortcutItem({
  item,
  identity,
  wrapAs,
  handleAction,
  wrapProps
}) {
  const { i18n, getSetting, setting, acl } = useGlobal();
  const typeList = getSetting('user.shortcut.sort_type') || {};

  const resourceMenu = useResourceMenu('user', 'shortcut', 'itemActionMenu');
  const menu = filterShowWhen(resourceMenu?.items, {
    item,
    acl,
    setting
  });

  if (!item) return null;

  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={identity}
      style={{ overflow: 'visible' }}
    >
      <ItemMedia>
        <UserAvatar
          user={{ ...item, cover: item?.cover ?? item?.avatar }}
          size={32}
        />
      </ItemMedia>
      <ItemText>
        <ItemTitle>{item.full_name}</ItemTitle>
        <ItemActionMenu
          disablePortal
          items={menu}
          identity={identity}
          handleAction={handleAction}
          placement="bottom-start"
          control={
            <TypographyStyled variant="body1">
              {typeList[item?.sort_type]
                ? i18n.formatMessage({ id: typeList[item?.sort_type] })
                : null}
              <LineIcon icon="ico-caret-down" />
            </TypographyStyled>
          }
        />
      </ItemText>
    </ItemView>
  );
}

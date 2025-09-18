import { Link, useGlobal, useResourceMenu } from '@metafox/framework';
import { APP_STORY, RESOURCE_STORY_MUTE } from '@metafox/story/constants';
import { StoryUserProps } from '@metafox/story/types';
import {
  ItemActionMenu,
  ItemMedia,
  ItemText,
  ItemTitle,
  ItemView,
  LineIcon,
  UserAvatar
} from '@metafox/ui';
import { filterShowWhen } from '@metafox/utils';
import { Button } from '@mui/material';
import * as React from 'react';
import { styled } from '@mui/material/styles';

const name = 'StoryMutedItem';

const WrapperButtonInline = styled('div', {
  name,
  slot: 'wrapperButtonInline',
  overridesResolver(props, styles) {
    return [styles.wrapperButtonInline];
  }
})(({ theme }) => ({
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'stretch',
  '& button': {
    marginLeft: theme.spacing(1),
    textTransform: 'capitalize',
    fontWeight: 'bold',
    whiteSpace: 'nowrap',
    borderRadius: theme.spacing(0.5),
    fontSize: theme.mixins.pxToRem(13),
    padding: theme.spacing(0.5, 1.25),
    marginBottom: theme.spacing(1),
    minWidth: theme.spacing(4),
    height: theme.spacing(4),
    '& .ico': {
      fontSize: theme.mixins.pxToRem(13)
    }
  },
  [theme.breakpoints.down('sm')]: {
    flexFlow: 'row wrap',
    padding: theme.spacing(0.5, 0),
    justifyContent: 'flex-start',
    '& button': {
      marginLeft: 0,
      marginRight: theme.spacing(1)
    }
  }
}));

export default function StoryMutedItem({
  item,
  wrapAs,
  wrapProps,
  handleAction
}: StoryUserProps) {
  const { useGetItem } = useGlobal();
  const menu: any = useResourceMenu(
    APP_STORY,
    RESOURCE_STORY_MUTE,
    'itemActionMenu'
  );

  const user = useGetItem(item?.owner);

  if (!item || !user) return null;

  const actionMenuItemsFull = filterShowWhen(menu.items, { item });
  const actionButtons = actionMenuItemsFull.slice(0, 1);
  const actionMenuItems = actionMenuItemsFull.slice(1);

  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps} testid="story-muted-item">
      <ItemMedia>
        <UserAvatar user={user} size={48} hoverCard={false} />
      </ItemMedia>
      <ItemText>
        <ItemTitle>
          <Link color={'inherit'} underline="none">
            {user?.full_name}
          </Link>
        </ItemTitle>
      </ItemText>
      <WrapperButtonInline>
        {actionButtons.map((btn, index) => (
          <Button
            key={btn.label}
            data-testid={btn?.name}
            variant={btn?.variant || (0 === index ? 'contained' : 'outlined')}
            startIcon={btn?.icon && <LineIcon icon={btn.icon} />}
            onClick={() => handleAction(btn.value)}
            color={(btn.color || 'primary') as any}
            size="small"
          >
            {btn.label}
          </Button>
        ))}
        {actionMenuItems?.length ? (
          <ItemActionMenu
            id="actionMenu"
            label="ActionMenu"
            handleAction={handleAction}
            items={actionMenuItems}
          />
        ) : null}
      </WrapperButtonInline>
    </ItemView>
  );
}

StoryMutedItem.displayName = 'StoryMutedMainCard';

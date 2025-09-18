import { menuStyles } from '@metafox/chatplus/constants';
import { MenuItems, MenuItemShape } from '@metafox/ui';
import { styled } from '@mui/material';
import { findIndex } from 'lodash';
import React from 'react';
import useStyles from './Toolbar.styles';

const name = 'ToolbarChat';

const Root = styled('div', {
  name,
  slot: 'root',
  shouldForwardProp: props => props !== 'variant'
})<{ variant?: string }>(({ theme, variant }) => ({
  display: 'flex',
  padding: '0',
  margin: '0',
  listStyle: 'none',
  ...(variant === 'pageMessage' && {
    '& .MuiListItem-root .ico': {
      fontSize: theme.mixins.pxToRem(13)
    }
  })
}));

interface Props {
  items: MenuItemShape[];
  handleAction: (value: string) => void;
  displayLimit?: number;
  variant?: 'roomPanel' | 'pageMessage' | string;
  popperOptions?: any;
}

export default function Toolbar({
  items,
  displayLimit = 4,
  variant = null,
  handleAction,
  popperOptions
}: Props) {
  const classes = useStyles();

  if (!Array.isArray(items)) return null;

  // dock chat
  if (variant === 'roomPanel') {
    let itemDisplay = displayLimit - 2;
    const foundPhone = findIndex(items, { testid: 'startVoiceCall' });
    const foundCall = findIndex(items, { testid: 'startVideoChat' });

    itemDisplay =
      displayLimit - 2 - (foundPhone > -1 ? 0 : 1) - (foundCall > -1 ? 0 : 1);

    const toolbarItems = items.filter((item, index) => {
      return index < itemDisplay || item.behavior;
    });

    const found = findIndex(toolbarItems, { behavior: 'more' });

    if (found > -1) {
      if (!toolbarItems[found].items?.length) {
        const subitems = items.filter(
          (item, index) => index > itemDisplay - 1 && !item.behavior
        );

        toolbarItems[found] = {
          ...toolbarItems[found],
          items: subitems,
          as: 'dropdown'
        };
      } else {
        toolbarItems[found].as = 'dropdown';
      }
    }

    return (
      <Root variant={variant}>
        <MenuItems
          items={toolbarItems}
          prefixName="chatplusToolbar.item."
          fallbackName="button"
          classes={classes}
          handleAction={handleAction}
          popperOptions={popperOptions}
          menuStyles={menuStyles}
        />
      </Root>
    );
  }

  // page chat
  if (variant === 'pageMessage') {
    const toolbarItems = items.filter((item, index) => {
      return item.behavior;
    });

    const found = findIndex(toolbarItems, { behavior: 'more' });

    if (found > -1) {
      if (!toolbarItems[found].items?.length) {
        const subitems = items.filter((item, index) => !item.behavior);

        toolbarItems[found] = {
          ...toolbarItems[found],
          items: subitems,
          as: 'dropdown'
        };
      } else {
        toolbarItems[found].as = 'dropdown';
      }
    }

    return (
      <Root variant={variant}>
        <MenuItems
          items={toolbarItems}
          prefixName="chatplusToolbar.item."
          fallbackName="button"
          classes={classes}
          handleAction={handleAction}
          variant={variant}
          popperOptions={popperOptions}
        />
      </Root>
    );
  }

  // default
  const toolbarItems = items.filter(
    (item, index) => index < displayLimit - 1 || item.behavior
  );

  const found = findIndex(toolbarItems, { behavior: 'more' });

  if (found > -1) {
    if (!toolbarItems[found].items?.length) {
      const subitems = items.filter(
        (item, index) => index > displayLimit - 2 && !item.behavior
      );

      toolbarItems[found] = {
        ...toolbarItems[found],
        items: subitems,
        as: 'dropdown'
      };
    } else {
      toolbarItems[found].as = 'dropdown';
    }
  }

  return (
    <Root>
      <MenuItems
        items={toolbarItems}
        prefixName="chatplusToolbar.item."
        fallbackName="button"
        classes={classes}
        handleAction={handleAction}
        popperOptions={popperOptions}
        menuStyles={menuStyles}
      />
    </Root>
  );
}

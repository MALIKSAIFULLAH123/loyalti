import { HandleAction, useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { Button, styled, Tooltip } from '@mui/material';
import React from 'react';
import ItemActionMenu from './ItemActionMenu';
import { IPropClickAction } from '@metafox/chatplus/types';

const name = 'MsgActionMenu';

const Root = styled('div', {
  name,
  slot: 'root'
})(({ theme }) => ({}));
const UIChatItemBtn = styled(Button, {
  name,
  slot: 'UIChatItemBtn',
  shouldForwardProp: props => props !== 'showHover'
})<{ showHover?: boolean }>(({ theme, showHover }) => ({
  position: 'relative',
  visibility: showHover ? 'hidden' : 'unset',
  padding: theme.spacing(1, 0.5),
  cursor: 'pointer',
  minWidth: theme.spacing(3),
  lineHeight: theme.spacing(2.5),
  color:
    theme.palette.mode === 'light'
      ? theme.palette.grey['600']
      : theme.palette.text.secondary
}));

const menuStyles = {
  overflowY: 'auto',
  overflowX: 'hidden',
  maxHeight: '260px',
  maxWidth: '170px',
  '&::-webkit-scrollbar': {
    height: '6px',
    width: '6px',
    borderRadius: '3px',
    transition: 'opacity 200ms'
  },

  /* Track */
  '&::-webkit-scrollbar-track': {
    borderRadius: '3px'
  },

  /* Handle */
  '&::-webkit-scrollbar-thumb': {
    backgroundColor: 'rgba(0,0,0,.2)',
    borderRadius: '3px'
  },

  '&::-webkit-scrollbar-thumb:horizontal': {
    background: '#000',
    borderRadius: '10px'
  }
};
interface Props {
  identity: string;
  handleAction: HandleAction;
  scrollRef: React.RefObject<HTMLDivElement>;
  items: any;
  showHover?: boolean;
  placement?: any;
  popperOptions?: any;
  onClickAction?: (obj: IPropClickAction) => void;
  showActionRef?: any;
  [key: string]: any;
}

export default function MsgActionMenu({
  identity,
  handleAction,
  scrollRef,
  items,
  showHover = true,
  placement = 'auto-start',
  menuStyles: menuStylesProps,
  ...rest
}: Props) {
  const { i18n } = useGlobal();

  return (
    <Root>
      <ItemActionMenu
        items={items}
        placement={placement}
        identity={identity}
        handleAction={handleAction}
        dependName="chatplus/msgActionMenu"
        scrollRef={scrollRef}
        scrollClose
        popperOptions={{
          strategy: 'fixed'
        }}
        variantPopper="hidden-outview"
        menuStyles={{ menuStyles, ...menuStylesProps }}
        {...rest}
        control={
          <Tooltip title={i18n.formatMessage({ id: 'more' })} placement="top">
            <UIChatItemBtn
              showHover={showHover}
              disableFocusRipple
              disableRipple
              disableTouchRipple
              className="uiChatItemBtn"
            >
              <LineIcon icon="ico-dottedmore-vertical-o" />
            </UIChatItemBtn>
          </Tooltip>
        }
      />
    </Root>
  );
}

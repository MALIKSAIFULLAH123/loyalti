import { HandleAction, useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { Button, styled, Tooltip } from '@mui/material';
import React from 'react';

const name = 'MsgActionMenu';

const Root = styled('div', {
  name,
  slot: 'root'
})(({ theme }) => ({
  '& .MuiActionMenu-menu': {
    overflow: 'auto',
    maxHeight: '220px'
  }
}));
const UIChatItemBtn = styled(Button, {
  name,
  slot: 'UIChatItemBtn'
})(({ theme }) => ({
  position: 'relative',
  visibility: 'hidden',
  padding: theme.spacing(1, 0.5),
  cursor: 'pointer',
  minWidth: theme.spacing(3),
  lineHeight: theme.spacing(2.5)
}));
interface Props {
  identity: string;
  handleAction: HandleAction;
  scrollRef: React.RefObject<HTMLDivElement>;
  items?: any[];
  placement?: any;
}

export default function MsgActionMenu({
  identity,
  handleAction,
  scrollRef,
  items,
  placement = 'auto-start'
}: Props) {
  const { ItemActionMenu, i18n } = useGlobal();

  return (
    <Root>
      <ItemActionMenu
        items={items}
        placement={placement}
        identity={identity}
        handleAction={handleAction}
        scrollRef={scrollRef}
        scrollClose
        popperOptions={{
          strategy: 'fixed'
        }}
        variantPopper="hidden-outview"
        control={
          <Tooltip title={i18n.formatMessage({ id: 'more' })} placement="top">
            <UIChatItemBtn
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

import { Paper, PopperProps, SxProps, styled } from '@mui/material';
import { camelCase } from 'lodash';
import React from 'react';
import ClickOutsideListener from './ClickOutsideListener';
import MenuItems from './MenuItems';
import Popper from './Popper';
import { MenuItemShape } from './types';

const PopperWrapper = styled(Popper, {
  name: 'PopperWrapper'
})<{}>(({ theme }) => ({
  zIndex: `${theme.zIndex.snackbar}!important`,
  minWidth: '180px',
  width: 'auto',
  maxHeight: '70vh',
  maxWidth: 300
}));

const PaperMenu = styled(Paper, {
  name: 'MuiActionMenu-menu'
})<{}>(({ theme }) => ({
  padding: theme.spacing(1, 0)
}));

export type ControlMenuControlProps = {
  onClick: () => void;
  'aria-label'?: string;
  'aria-controls'?: string;
  'aria-haspopup'?: string;
  'aria-expanded'?: boolean;
  ref?: any;
  component?: React.ElementType;
};

export type ControlMenuProps = {
  id: string;
  testid?: string;
  label: string;
  identity?: string;
  open: PopperProps['open'];
  control: JSX.Element;
  placement?: PopperProps['placement'];
  items: MenuItemShape[];
  handleAction?: any;
  onClose?: () => void;
  onOpen?: () => void;
  disablePortal?: boolean;
  scrollRef?: React.RefObject<HTMLDivElement>;
  scrollClose?: boolean;
  handleClickOuter?: () => void;
  prefixName?: string;
  fallbackName?: string;
  classes?: Record<string, any>;
  preventDefault?: boolean;
  sx?: SxProps;
  zIndex?: number;
  popperOptions?: any;
  popperStyles?: Record<string, any>;
  menuStyles?: Record<string, any>;
  variantPopper?: 'hidden-outview' | string;
};

export default function ControlMenu({
  open,
  id,
  identity,
  testid,
  control,
  label,
  placement,
  handleAction,
  onClose,
  onOpen,
  disablePortal = false,
  classes,
  items,
  scrollRef,
  scrollClose,
  prefixName,
  fallbackName,
  preventDefault,
  sx,
  zIndex,
  popperOptions,
  popperStyles = {},
  menuStyles = {},
  variantPopper
}: ControlMenuProps) {
  const anchorRef = React.useRef<HTMLDivElement>();
  const paperRef = React.useRef();

  const handleOutsideClick = React.useCallback(
    (evt: Event) => {
      if (evt) {
        // some time this cause issue for native input trigger etc: inputRef.current.click() may not work.
        // evt.stopPropagation();
        // evt.preventDefault();
      }

      if (open) onClose();
    },
    [onClose, open]
  );

  React.useEffect(() => {
    if (open && scrollRef?.current && scrollClose) {
      const off = () => onClose();
      scrollRef?.current.addEventListener('scroll', off);

      return () => {
        scrollRef?.current?.removeEventListener('scroll', off);
      };
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [open]);

  // require for prevent outer click
  const handleOnClick = React.useCallback(
    (evt: Event) => {
      if (evt?.stopPropagation && preventDefault) {
        evt.stopPropagation();
        evt.preventDefault();
      }

      onOpen();
    },
    [onOpen, preventDefault]
  );

  // reduce render logic as much as possible
  return (
    <>
      {React.cloneElement(control, {
        'aria-label': label,
        'aria-controls': id,
        'data-testid': camelCase(`${testid ?? id} button`),
        'aria-haspopup': 'menu',
        'aria-expanded': !!open,
        onClick: handleOnClick,
        ref: anchorRef,
        sx
      })}
      {open ? (
        <ClickOutsideListener
          onClickAway={handleOutsideClick}
          excludeRef={anchorRef}
        >
          <PopperWrapper
            id={id}
            data-testid={testid ?? id}
            open={Boolean(open)}
            zIndex={zIndex}
            sx={popperStyles}
            anchorEl={anchorRef.current}
            disablePortal={disablePortal}
            placement={placement ?? 'bottom-end'}
            popperOptions={popperOptions}
            variant={variantPopper}
          >
            <PaperMenu sx={menuStyles} ref={paperRef}>
              <MenuItems
                items={items}
                closeMenu={onClose}
                fallbackName={fallbackName}
                prefixName={prefixName}
                handleAction={handleAction}
                identity={identity}
                variant="dropdown"
                classes={classes}
              />
            </PaperMenu>
          </PopperWrapper>
        </ClickOutsideListener>
      ) : null}
    </>
  );
}

import { ClickOutsideListener, MenuItemShape } from '@metafox/ui';
import { Box, List, Paper, Popper } from '@mui/material';
import { debounce } from 'lodash';
import React, { useRef } from 'react';

interface Props {
  activeTab: string;
  items: MenuItemShape[];
  MenuItem: React.FC<{
    item: MenuItemShape;
    activeTab?: string;
  }>;
  SecondMenuItem: React.FC<{ item: MenuItemShape; activeTab?: string; closeMenu?: any }>;
  MoreButton: React.FC<{ onClick: any }>;
}

type Ref = React.MutableRefObject<HTMLDivElement>;

export function useAutoCompactMenu(
  rootRef: Ref,
  reserve: number,
  menuName: string
): number {
  const [resizeIndex, setResizeIndex] = React.useState<number>(-1);
  const [widthMap, setWidths] = React.useState<number[]>([]);
  const [maxWidth, setMaxWidth] = React.useState<number>(0);
  const onResize = React.useCallback(() => {
    if (!rootRef.current) return;

    const maxWidth = rootRef.current.getBoundingClientRect().width - reserve;
    setMaxWidth(maxWidth);

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  React.useEffect(() => {
    if (resizeIndex === -1) {
      // calculate when mount or need reset (index === -1)
      const widths = [];
      const cc: HTMLCollection = rootRef.current?.children;

      for (let i = 0; i < cc.length; i++) {
        const cw = cc[i].getBoundingClientRect().width;
        widths[i] = cw;
      }

      setWidths(widths);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [resizeIndex]);

  React.useEffect(() => {
    // reset calculate when change menu
    setResizeIndex(-1);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [menuName]);

  React.useEffect((): (() => void) | void => {
    if ('object' === typeof window) {
      window.addEventListener('resize', debounce(onResize, 100));

      onResize();

      return () => {
        window.removeEventListener('resize', debounce(onResize, 100));
      };
    }
  }, [onResize]);

  React.useEffect(() => {
    let width: number = 0;
    let index: number = 0;

    for (let i = 0; i < widthMap.length; ++i) {
      if (width + widthMap[i] > maxWidth) break;

      width += widthMap[i];
      index = i;
    }

    setResizeIndex(index);
  }, [widthMap, maxWidth]);

  // console.log(maxWidth, widthMap, resizeIndex);

  return resizeIndex;
}

const invisibleStyle: React.CSSProperties = { visibility: 'hidden' };
const MORE_BUTTON_WIDTH = 100;

function Item({ hidden, visible, children }) {
  if (hidden) {
    return null;
  }

  if (!visible) {
    return <div style={invisibleStyle}>{children}</div>;
  }

  return children;
}

function AutoCompactMenu({
  items,
  activeTab,
  MenuItem,
  SecondMenuItem,
  MoreButton,
  menuName
}: Props) {
  const [open, setOpen] = React.useState<boolean>(false);
  const refMenuMore = useRef<HTMLButtonElement>();
  const rootRef = useRef<HTMLDivElement>();
  const resizeIndex = useAutoCompactMenu(rootRef, MORE_BUTTON_WIDTH, menuName);

  const toggleOpen = React.useCallback(
    () => setOpen((prev: boolean) => !prev),
    []
  );
  const closeMenu = React.useCallback(() => setOpen(false), []);
  const count = items?.length;

  if (!count) return null;

  const hasSecondMenu = resizeIndex > -1 && resizeIndex < count;

  return (
    <Box
      sx={{
        flex: 1,
        display: 'flex',
        width: '100%',
        overflow: 'hidden',
        alignItems: 'center',
        justifyContent: 'flex-end',
        flexWrap: 'nowrap'
      }}
      ref={rootRef}
    >
      {items.map((item, index) => (
        <Item
          hidden={resizeIndex > -1 && index >= resizeIndex}
          key={index.toString()}
          visible={index < resizeIndex}
        >
          <MenuItem item={item} activeTab={activeTab} key={index.toString()} />
        </Item>
      ))}
      <ClickOutsideListener onClickAway={closeMenu}>
        <Box
          sx={{
            margin: 0,
            display: 'inline-block',
            padding: 0,
            listStyle: 'none none outside'
          }}
        >
          {hasSecondMenu ? (
            <MoreButton ref={refMenuMore} onClick={toggleOpen} />
          ) : null}
          <Popper
            open={open && -1 < resizeIndex}
            anchorEl={refMenuMore.current}
            style={{
              zIndex: 1200
            }}
          >
            <Paper elevation={3} sx={{ py: 1, mr: 1 }}>
              <List>
                {items.slice(resizeIndex).map((item, index) => (
                  <SecondMenuItem
                    item={item}
                    activeTab={activeTab}
                    closeMenu={closeMenu}
                    key={index.toString()}
                  />
                ))}
              </List>
            </Paper>
          </Popper>
        </Box>
      </ClickOutsideListener>
    </Box>
  );
}

export default React.memo<Props>(
  AutoCompactMenu,
  (prev: Props, next: Props) => next.items === prev.items
);

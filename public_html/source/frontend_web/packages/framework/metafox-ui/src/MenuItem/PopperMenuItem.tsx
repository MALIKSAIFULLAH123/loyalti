/**
 * @type: ui
 * name: menuItem.as.popover
 * chunkName: menuItemAs
 */
import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { Badge, Popper } from '@mui/material';
import clsx from 'clsx';
import * as React from 'react';
import ClickOutsideListener from '../ClickOutsideListener';

export default function PopperMenuItem({ item, classes }: any) {
  const { jsxBackend } = useGlobal();
  const containerRef = React.useRef(null);
  const [open, setOpen] = React.useState<boolean>(false);
  const toggleOpen = () => setOpen((prev: boolean) => !prev);

  const handleClickAway = () => {
    setOpen(false);
  };

  return (
    <ClickOutsideListener onClickAway={handleClickAway}>
      <div className={classes.menuRefIndex} ref={containerRef}>
        <span
          role="button"
          onClick={toggleOpen}
          style={{ position: 'relative', display: 'inline-block' }}
          className={clsx(
            classes.smallMenuButton,
            item.active || open ? classes.menuButtonActive : false
          )}
        >
          <Badge color="error">
            <LineIcon className={classes.smallMenuIcon} icon={item.icon} />
          </Badge>
        </span>
        <Popper
          id="notifications"
          open={open}
          anchorEl={containerRef.current}
          disablePortal
          placement="bottom-end"
          className={classes.popper}
        >
          {jsxBackend.render(item.content)}
        </Popper>
      </div>
    </ClickOutsideListener>
  );
}

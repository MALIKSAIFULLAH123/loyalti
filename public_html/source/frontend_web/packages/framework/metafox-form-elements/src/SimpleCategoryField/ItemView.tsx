import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { IconButton, styled } from '@mui/material';
import clsx from 'clsx';
import { isEmpty } from 'lodash';
import * as React from 'react';

const name = 'SimpleCategoryField';

const ItemLink = styled('div', {
  name,
  slot: 'itemLink',
  overridesResolver(props, styles) {
    return [styles.itemLink];
  }
})(({ theme }) => ({
  cursor: 'pointer',
  fontSize: theme.mixins.pxToRem(15),
  display: 'flex',
  flexDirection: 'row',
  alignItems: 'center',
  color:
    theme.palette.mode === 'light'
      ? theme.palette.text.primary
      : theme.palette.text.secondary,
  textDecoration: 'none',
  position: 'relative'
}));
export interface CategoryItemShape {
  id: number;
  name: string;
  resource_name: string;
  subs: CategoryItemShape[];
}

export interface CategoryItemViewProps {
  item: CategoryItemShape;
  handleSelect: (id: string, name: string) => void;
  active?: boolean;
  [key: string]: any;
}

type TCategoryItemClassKey = Record<
  'subCategory' | 'link' | 'item' | 'itemActive' | 'span' | 'icon' | 'hasSubs',
  string
>;

type TCategoryItemViewStyles = { classes?: TCategoryItemClassKey };

export default function ItemView({
  item,
  handleSelect,
  active,
  classes
}: CategoryItemViewProps & TCategoryItemViewStyles) {
  const [open, setOpen] = React.useState<boolean>(false);
  const { usePageParams } = useGlobal();
  const { category_id, forum_id } = usePageParams();
  const idActive = category_id ?? forum_id;
  const [activeCollapsed, setActive] = React.useState<boolean>(false);

  React.useEffect(() => {
    if (
      item?.subs?.length > 0 &&
      item.subs.filter(item => item.id.toString() === idActive).length > 0
    ) {
      setOpen(true);
      setActive(true);

      return;
    }

    setActive(false);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [idActive]);

  const toggleSub = React.useCallback(() => {
    setOpen(prev => !prev);
  }, []);

  return (
    <div className={classes.item}>
      <div
        className={clsx(
          !isEmpty(item.subs) && classes.hasSubs,
          (active || (activeCollapsed && !open)) && classes.itemActive
        )}
      >
        <ItemLink
          data-testid="itemCategory"
          onClick={() => handleSelect(item.id, item.resource_name)}
          className={classes.link}
          color={'inherit'}
          aria-selected={active}
          aria-label={item.name}
        >
          <span className={classes.span}>{item.name}</span>
        </ItemLink>
        {isEmpty(item.subs) ? null : (
          <IconButton size="small" className={classes.icon} onClick={toggleSub}>
            <LineIcon icon={open ? 'ico-angle-up' : 'ico-angle-down'} />
          </IconButton>
        )}
      </div>
      {open && item?.subs?.length > 0 ? (
        <ul className={classes.subCategory}>
          {item?.subs.map((item, index) => (
            <ItemView
              item={item}
              key={index.toString()}
              classes={classes}
              handleSelect={() => handleSelect(item.id, item.resource_name)}
              active={item.id.toString() === idActive}
            />
          ))}
        </ul>
      ) : null}
    </div>
  );
}

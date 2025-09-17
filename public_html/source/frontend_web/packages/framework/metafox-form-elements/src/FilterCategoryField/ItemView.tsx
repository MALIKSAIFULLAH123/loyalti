import { LineIcon } from '@metafox/ui';
import { Box, IconButton, styled } from '@mui/material';
import clsx from 'clsx';
import { isEmpty } from 'lodash';
import * as React from 'react';

const Item = styled('div', {
  name: 'FilterCategoryField',
  slot: 'itemCategoryFilter',
  overridesResolver(props, styles) {
    return [styles.itemCategoryFilter];
  }
})(({ theme }) => ({
  '&.hasSubs': {
    display: 'flex',
    alignItems: 'center',
    '& > div': {
      paddingRight: theme.spacing(5)
    }
  },
  '&.itemActive': {
    '& > div': {
      color: theme.palette.primary.main,
      fontWeight: theme.typography.fontWeightBold
    }
  }
}));

const Link = styled('div')(({ theme }) => ({
  minHeight: theme.spacing(7),
  flex: 1,
  minWidth: 0,
  padding: theme.spacing(1, 2),
  '&:hover': {
    backgroundColor: theme.palette.action.selected,
    borderRadius: theme.shape.borderRadius
  },
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

const Icon = styled(IconButton)(({ theme }) => ({
  position: 'absolute',
  width: 32,
  height: 32,
  right: theme.spacing(1),
  '& .ico': {
    fontSize: theme.mixins.pxToRem(15),
    paddingLeft: 0
  }
}));

const SubCategory = styled('ul')(({ theme }) => ({
  borderLeft: `1px solid ${theme.palette.border.secondary}`,
  listStyle: 'none',
  margin: theme.spacing(0, 0, 0, 2),
  padding: '0 0 0 2px'
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

const checkSubNestedActive = (data, idActive) => {
  if (data?.subs?.length > 0) {
    if (
      data.subs.some(
        x => x.id.toString() === idActive || checkSubNestedActive(x, idActive)
      )
    )
      return true;
  }

  return false;
};

export default function ItemView({
  item,
  handleSelect,
  active,
  classes,
  category_id
}: CategoryItemViewProps & TCategoryItemViewStyles) {
  const [open, setOpen] = React.useState<boolean>(false);
  const idActive = category_id;

  React.useEffect(() => {
    if (checkSubNestedActive(item, idActive)) {
      setOpen(true);

      return;
    }

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [idActive]);

  const toggleSub = React.useCallback(() => {
    setOpen(prev => !prev);
  }, []);

  const handleChangeSelectItem = (id, resource_name) => {
    handleSelect(id, resource_name);
  };

  return (
    <Box>
      <Item
        className={clsx(
          !isEmpty(item.subs) && 'hasSubs',
          active && 'itemActive'
        )}
      >
        <Link
          onClick={() => handleChangeSelectItem(item.id, item.resource_name)}
          data-testid="itemCategory"
          color={'inherit'}
          aria-selected={active}
          aria-label={item.name}
        >
          <span>{item.name}</span>
        </Link>
        {isEmpty(item.subs) ? null : (
          <Icon size="small" onClick={toggleSub}>
            <LineIcon icon={open ? 'ico-angle-up' : 'ico-angle-down'} />
          </Icon>
        )}
      </Item>
      {open && item?.subs?.length > 0 ? (
        <SubCategory>
          {item?.subs.map((subItem, index) => {
            return (
              <div key={subItem.id}>
                <ItemView
                  item={subItem}
                  classes={classes}
                  handleSelect={handleChangeSelectItem}
                  active={subItem.id.toString() === idActive}
                  category_id={category_id}
                />
              </div>
            );
          })}
        </SubCategory>
      ) : null}
    </Box>
  );
}

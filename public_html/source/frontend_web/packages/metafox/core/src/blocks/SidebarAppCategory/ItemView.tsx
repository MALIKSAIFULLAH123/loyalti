import { RouteLink, useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { IconButton, styled } from '@mui/material';
import clsx from 'clsx';
import { isEmpty } from 'lodash';
import * as React from 'react';
import checkActiveMenu from './checkActiveMenu';

const ItemWrapper = styled('div', {
  name: 'LayoutSlot',
  slot: 'categoryItem',
  overridesResolver(props, styles) {
    return [styles.categoryItem];
  }
})(({ theme }) => ({
  '& a': {
    minHeight: theme.spacing(7),
    flex: 1,
    minWidth: 0,
    padding: theme.spacing(1, 2),
    '&:hover': {
      backgroundColor: theme.palette.action.selected,
      borderRadius: theme.shape.borderRadius
    }
  }
}));

const Item = styled('div')(({ theme }) => ({
  '&.hasSubs': {
    display: 'flex',
    alignItems: 'center',
    '& a': {
      paddingRight: theme.spacing(5)
    }
  },
  '&.itemActive': {
    '& > a': {
      color: theme.palette.primary.main,
      fontWeight: theme.typography.fontWeightBold
    }
  }
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

const Link = styled(RouteLink)(({ theme }) => ({
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

const SubCategory = styled('ul')(({ theme }) => ({
  borderLeft: `1px solid ${theme.palette.border.secondary}`,
  listStyle: 'none',
  margin: theme.spacing(0, 0, 0, 2),
  padding: 0
}));

export type CategoryItemViewProps = {
  name: string;
  subs?: CategoryItemViewProps[];
  active?: boolean;
  resource_name?: string;
  id: string;
  link?: string;
  [key: string]: any;
};

type TCategoryItemClassKey = Record<
  'subCategory' | 'link' | 'item' | 'itemActive' | 'span' | 'icon' | 'hasSubs',
  string
>;

type TCategoryItemViewStyles = { classes?: TCategoryItemClassKey };

const checkSubNestedActive = (subs, pageParams, category) => {
  if (subs?.length > 0) {
    return subs.some(
      x =>
        category === x?.id?.toString() ||
        checkActiveMenu(pageParams, x) ||
        checkSubNestedActive(x.subs, pageParams, category)
    );
  }

  return false;
};

export default function ItemView({
  name,
  id,
  subs,
  resource_name,
  active,
  classes,
  link
}: CategoryItemViewProps & TCategoryItemViewStyles) {
  const { usePageParams } = useGlobal();

  const pageParams = usePageParams();
  const { category } = pageParams || {};
  const [open, setOpen] = React.useState<boolean>(
    checkSubNestedActive(subs, pageParams, category)
  );
  const toggleSub = React.useCallback(() => {
    setOpen(prev => !prev);
  }, []);

  React.useEffect(() => {
    if (checkSubNestedActive(subs, pageParams, category)) {
      setOpen(true);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [category, pageParams?.id]);

  return (
    <ItemWrapper>
      <Item
        className={clsx(!isEmpty(subs) && 'hasSubs', active && 'itemActive')}
      >
        <Link
          to={link}
          data-testid="itemCategory"
          color={'inherit'}
          aria-selected={active}
          aria-label={name}
        >
          <span>{name}</span>
        </Link>
        {isEmpty(subs) ? null : (
          <Icon size="small" onClick={toggleSub}>
            <LineIcon icon={open ? 'ico-angle-up' : 'ico-angle-down'} />
          </Icon>
        )}
      </Item>
      {open && subs && subs?.length > 0 ? (
        <SubCategory>
          {subs.map((item, index) => (
            <ItemView
              key={index.toString()}
              classes={classes}
              link={item?.link || item?.url}
              active={
                category === item.id.toString() ||
                checkActiveMenu(pageParams, item)
              }
              {...item}
            />
          ))}
        </SubCategory>
      ) : null}
    </ItemWrapper>
  );
}

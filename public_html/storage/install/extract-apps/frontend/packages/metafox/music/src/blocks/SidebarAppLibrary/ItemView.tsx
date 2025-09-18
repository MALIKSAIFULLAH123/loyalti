import { RouteLink, useGlobal } from '@metafox/framework';
import { ItemAction, ItemActionMenu, TruncateText } from '@metafox/ui';
import { styled } from '@mui/material';
import clsx from 'clsx';
import * as React from 'react';
import { PlaylistItemProps } from '@metafox/music/types';
import { RESOURCE_PLAYLIST } from '@metafox/music/constant';

const ItemWrapper = styled('div')(({ theme }) => ({
  position: 'relative',
  width: '100%',
  '&:hover': {
    backgroundColor: theme.palette.action.selected,
    borderRadius: theme.shape.borderRadius,
    '& .ItemView-action': {
      opacity: 1
    }
  },
  '& a': {
    height: theme.spacing(7),
    flex: 1,
    minWidth: 0,
    padding: theme.spacing(0, 2)
  }
}));

const Item = styled('div')(({ theme }) => ({
  marginRight: theme.spacing(1),
  '&.hasSubs': {
    display: 'flex',
    alignItems: 'center'
  },
  '&.itemActive div': {
    fontWeight: 700,
    color: theme.palette.primary.main
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

const ItemActionWrapper = styled(ItemAction, {
  name: 'ItemActionWrapper'
})(({ theme }) => ({
  opacity: 0,
  top: theme.spacing(-0.5),
  [theme.breakpoints.down('sm')]: {
    padding: theme.spacing(0, 0, 0, 2),
    top: theme.spacing(1.5),
    right: theme.spacing(1),
    opacity: 1
  }
}));

export type CategoryItemViewProps = {
  name: string;
  active?: boolean;
  link?: string;
  [key: string]: any;
};

type TCategoryItemClassKey = Record<
  'link' | 'item' | 'itemActive' | 'span' | 'icon',
  string
>;

type TCategoryItemViewStyles = { classes?: TCategoryItemClassKey };

export default function ItemView({
  name,
  active,
  link,
  identity,
  item,
  handleAction
}: CategoryItemViewProps & TCategoryItemViewStyles & PlaylistItemProps) {
  const { usePageParams } = useGlobal();
  const { id, resource_name } = usePageParams();

  return (
    <ItemWrapper>
      <Item
        className={clsx(
          id === item.id.toString() &&
            resource_name === RESOURCE_PLAYLIST &&
            'itemActive'
        )}
      >
        <Link
          to={item.link}
          data-testid="itemCategory"
          color={'inherit'}
          aria-label={item.name}
        >
          <TruncateText lines={2}>{item.name}</TruncateText>
        </Link>
      </Item>
      <ItemActionWrapper placement="top-end" spacing="normal">
        <ItemActionMenu
          identity={identity}
          icon={'ico-dottedmore-vertical-o'}
          handleAction={handleAction}
        />
      </ItemActionWrapper>
    </ItemWrapper>
  );
}

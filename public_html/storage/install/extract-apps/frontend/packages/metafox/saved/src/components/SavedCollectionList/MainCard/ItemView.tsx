import { Link, useGlobal } from '@metafox/framework';
import {
  ItemAction,
  ItemMedia,
  ItemTitle,
  ItemView,
  LineIcon
} from '@metafox/ui';
import { styled } from '@mui/material';
import React from 'react';

const ItemWrapperContent = styled('div')(({ theme }) => ({
  flex: 1,
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',
  overflow: 'hidden'
}));
const WrapperLink = styled(Link)(({ theme }) => ({
  flex: 1,
  display: 'flex',
  alignItems: 'center',
  overflow: 'hidden',
  height: '100%'
}));

const WrapperTitle = styled(ItemTitle)(({ theme }) => ({
  paddingLeft: theme.spacing(1),
  marginBottom: theme.spacing(0),
  '& > p': {
    whiteSpace: 'inherit'
  }
}));

const ItemMediaWrapper = styled(ItemMedia)(({ theme }) => ({
  minWidth: theme.spacing(2)
}));

function CollectionItemMainCard({
  item,
  identity,
  actions,
  handleAction,
  state,
  itemProps,
  user,
  wrapAs,
  wrapProps
}) {
  const { ItemActionMenu, usePageParams, useIsMobile } = useGlobal();

  const isMobile = useIsMobile();

  const { collection_id } = usePageParams();

  // eslint-disable-next-line eqeqeq
  const selected = collection_id == item.id;

  if (!item) return null;

  const to = `/saved/list/${item.id}`;

  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={identity}
      color="inherit"
    >
      <ItemWrapperContent>
        <WrapperLink
          color={selected ? 'primary' : 'inherit'}
          to={to}
          underline="none"
        >
          <ItemMediaWrapper>
            <LineIcon
              icon="ico-folder-alt-o"
              sx={{ fontSize: '1rem' }}
              color={selected ? 'primary' : 'textPrimary'}
            />
          </ItemMediaWrapper>
          <WrapperTitle
            color={selected ? 'primary' : 'textPrimary'}
            sx={{ wordBreak: 'break-all' }}
          >
            {item.name}
          </WrapperTitle>
        </WrapperLink>
        {itemProps.showActionMenu && (
          <ItemAction visible={isMobile ? 'always' : 'hover'}>
            <ItemActionMenu
              identity={identity}
              icon={'ico-dottedmore-vertical-o'}
              state={state}
              handleAction={handleAction}
              zIndex={1300}
            />
          </ItemAction>
        )}
      </ItemWrapperContent>
    </ItemView>
  );
}

export default CollectionItemMainCard;

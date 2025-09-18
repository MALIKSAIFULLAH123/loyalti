import { Link, useGlobal, getItemsSelector } from '@metafox/framework';
import React from 'react';
import { ItemText, ItemTitle, ItemView, LineIcon } from '@metafox/ui';
import { styled, Box, Button, MenuItem, Menu } from '@mui/material';
import LoadingSkeleton from './LoadingSkeleton';
import { slugify } from '@metafox/utils';
import { useSelector } from 'react-redux';

const name = 'ForumItemMainCard';

const SubInfoStyled = styled('div', { name, slot: 'subInfoStyled' })(
  ({ theme }) => ({
    fontWeight: 'unset'
  })
);

const ItemTextStyled = styled(ItemText, { name, slot: 'ItemTextStyled' })(
  ({ theme }) => ({
    flexDirection: 'row',
    justifyContent: 'space-between'
  })
);

const TitleInfoStyled = styled('div', { name, slot: 'titleInfoStyled' })(
  ({ theme }) => ({
    display: 'flex',
    flexDirection: 'column',
    alignSelf: 'center'
  })
);

export default function ForumItemMainCard({
  item,
  identity,
  itemProps,
  user,
  state,
  handleAction,
  wrapAs,
  wrapProps
}) {
  const { i18n } = useGlobal();
  const to = `/forum/${item?.id}/${slugify(item?.title)}`;
  const [anchorEl, setAnchorEl] = React.useState<null | HTMLElement>(null);
  const open = Boolean(anchorEl);

  const handleClick = (event: React.MouseEvent<HTMLButtonElement>) => {
    setAnchorEl(event.currentTarget);
  };

  const handleClose = () => {
    setAnchorEl(null);
  };

  const { title, subs } = item;

  const subsEntities = useSelector((state: GlobalState) =>
    getItemsSelector(state, subs)
  );

  return (
    <ItemView testid={item.resource_name} wrapAs={wrapAs} wrapProps={wrapProps}>
      <ItemTextStyled>
        <TitleInfoStyled>
          <ItemTitle>
            <Link to={to}>{title}</Link>
          </ItemTitle>
          {subs && subsEntities?.length ? (
            <SubInfoStyled>
              <Button
                sx={{
                  color: 'text.secondary',
                  fontWeight: 'unset',
                  padding: 0,
                  background: 'transparent !important'
                }}
                onClick={handleClick}
                id="menu-sub"
              >
                {i18n.formatMessage({ id: 'sub_forums' })}
                <Box ml={1}>
                  <LineIcon
                    icon={open ? 'ico-caret-up' : 'ico-caret-down'}
                    sx={{ fontSize: 13 }}
                  />
                </Box>
              </Button>
              <Menu
                id="menu-sub"
                anchorEl={anchorEl}
                open={open}
                onClose={handleClose}
                anchorOrigin={{
                  vertical: 'bottom',
                  horizontal: 'left'
                }}
              >
                {subsEntities.map((sub, index) => (
                  <MenuItem onClick={handleClose} key={index}>
                    <Link to={`/forum/${sub?.id}/${slugify(sub?.title)}`}>
                      {sub?.title}
                    </Link>
                  </MenuItem>
                ))}
              </Menu>
            </SubInfoStyled>
          ) : null}
        </TitleInfoStyled>
      </ItemTextStyled>
    </ItemView>
  );
}
ForumItemMainCard.LoadingSkeleton = LoadingSkeleton;
ForumItemMainCard.displayName = 'ForumItem(MainCard)';

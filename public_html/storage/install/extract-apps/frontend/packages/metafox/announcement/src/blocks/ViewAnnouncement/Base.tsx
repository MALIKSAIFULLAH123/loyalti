import { Link, useGlobal } from '@metafox/framework';
// utils
import HtmlViewer from '@metafox/html-viewer';
// layout
import { Block, BlockContent } from '@metafox/layout';
// types
import { AnnouncementItemProps as ItemProps } from '@metafox/announcement/types';
// components
import {
  DotSeparator,
  FormatDate,
  ItemTitle,
  ItemUserShape,
  UserAvatar
} from '@metafox/ui';
import { Box, Button, styled, Typography } from '@mui/material';
import React from 'react';
import LoadingSkeleton from './LoadingSkeleton';

const ItemTitleStyled = styled(ItemTitle, {
  name: 'ItemView',
  shouldForwardProp: prop => prop !== 'type'
})<{ type?: string }>(({ theme, type }) => ({
  ...(type === 'danger' && {
    color: theme.palette.error.dark
  }),
  ...(type === 'success' && {
    color: theme.palette.success.dark
  }),
  ...(type === 'warning' && {
    color: theme.palette.warning.dark
  }),
  ...(type === 'info' && {
    color: theme.palette.info.dark
  })
}));

const UserRead = styled('span', {
  name: 'userRead',
  shouldForwardProp: (prop: string) => prop !== 'isPointer'
})<{ isPointer: boolean }>(({ theme, isPointer }) => ({
  marginLeft: theme.spacing(0.5),
  cursor: isPointer ? 'pointer' : 'default',
  '&:hover': {
    textDecoration: isPointer ? 'underline' : 'none'
  },
  ...(isPointer && {
    color: theme.palette.primary.main
  })
}));

const HeaderProfileInfo = styled(Box, { slot: 'HeaderProfileInfo' })(
  ({ theme }) => ({
    display: 'flex',
    marginTop: theme.spacing(2)
  })
);

const AvatarWrapper = styled('div', { name: 'AvatarWrapper' })(({ theme }) => ({
  marginRight: theme.spacing(1.5)
}));

const HeaderSubInfo = styled(Box, { name: 'HeaderSubInfo' })(({ theme }) => ({
  display: 'flex',
  flexDirection: 'column',
  justifyContent: 'center'
}));

export default function DetailView({
  user,
  item,
  identity,
  state,
  handleAction
}: ItemProps) {
  const { ItemDetailInteraction, i18n, dispatch, useLoggedIn } = useGlobal();
  const loggedIn = useLoggedIn();

  if (!user || !item) return null;

  const openListViewer = () => {
    if (item?.statistic?.total_view)
      dispatch({ type: 'announcement/openListViewer', payload: { identity } });
  };

  const onMarkAsRead = () => {
    if (item.is_read) return;

    dispatch({
      type: 'announcement/markAsRead',
      payload: { id: item.id, isDetail: true }
    });
  };

  return (
    <Block testid={`detailview ${item.resource_name}`}>
      <BlockContent>
        <Box p={2} bgcolor="background.paper">
          <ItemTitleStyled
            variant="h3"
            component={'div'}
            pr={2}
            showFull
            type={item?.style}
          >
            <Typography
              component="h1"
              variant="h3"
              sx={{
                pr: 2.5,
                display: { sm: 'inline', xs: 'block' },
                mt: { sm: 0, xs: 1 },
                verticalAlign: 'middle'
              }}
            >
              {item?.title}
            </Typography>
          </ItemTitleStyled>
          <HeaderProfileInfo>
            <AvatarWrapper>
              <UserAvatar user={user as ItemUserShape} size={48} />
            </AvatarWrapper>
            <HeaderSubInfo>
              <Link
                to={user.link}
                children={user?.full_name}
                hoverCard={`/user/${user.id}`}
                color="text.primary"
                sx={{ fontSize: 15, fontWeight: 'bold', display: 'block' }}
              />
              <DotSeparator sx={{ color: 'text.secondary', mt: 0.5 }}>
                <FormatDate
                  data-testid="publishedDate"
                  value={item?.creation_date}
                  format="LL"
                />
              </DotSeparator>
            </HeaderSubInfo>
          </HeaderProfileInfo>
          <Box component="div" mt={2} fontSize="15px">
            <HtmlViewer html={item?.text_parsed || ''} />
            <Box mt={2} sx={{ display: 'flex', alignItems: 'center' }}>
              {loggedIn ? (
                <Box mr={1}>
                  <Button
                    variant="outlined"
                    size="medium"
                    color="primary"
                    disabled={item?.is_read}
                    onClick={onMarkAsRead}
                  >
                    {i18n.formatMessage({
                      id: item?.is_read ? 'i_have_read_this' : 'mark_as_read'
                    })}
                  </Button>
                </Box>
              ) : null}
              <Box>
                {i18n.formatMessage({ id: 'read_by' })}
                <UserRead
                  onClick={openListViewer}
                  isPointer={Boolean(item?.statistic?.total_view)}
                >
                  {i18n.formatMessage(
                    { id: 'number_user' },
                    { value: item?.statistic?.total_view }
                  )}
                </UserRead>
              </Box>
            </Box>
          </Box>
          <ItemDetailInteraction
            identity={identity}
            state={state}
            handleAction={handleAction}
          />
        </Box>
      </BlockContent>
    </Block>
  );
}

DetailView.LoadingSkeleton = LoadingSkeleton;
DetailView.displayName = 'Announcement_DetailView';

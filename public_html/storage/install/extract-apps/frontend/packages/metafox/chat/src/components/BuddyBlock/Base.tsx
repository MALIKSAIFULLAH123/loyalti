import { BlockViewProps, useGlobal } from '@metafox/framework';
import { ScrollContainer, SearchBox } from '@metafox/layout';
import { Box, styled, Typography } from '@mui/material';
import { camelCase } from 'lodash';
import React, { useState } from 'react';

export interface Props extends BlockViewProps {}

const name = 'BuddyBlock';

const Root = styled(Box, {
  name,
  slot: 'root',
  overridesResolver: (props, styles) => [styles.root]
})(({ theme }) => ({
  backgroundColor: theme.palette.background.paper,
  width: '100%',
  height: '100%',
  display: 'flex',
  flexDirection: 'column'
}));

const WrapperHeader = styled('div', {
  name,
  slot: 'WrapperHeader',
  overridesResolver: (props, styles) => [styles.wrapperHeader]
})(({ theme }) => ({}));
const HeaderStyled = styled('div')(({ theme }) => ({
  alignItems: 'center',
  boxSizing: 'border-box',
  display: 'flex',
  padding: theme.spacing(3, 2, 1),
  justifyContent: 'space-between'
}));

const BlockSearchStyled = styled('div', {
  name,
  slot: 'BlockSearch',
  overridesResolver: (props, styles) => [styles.blockSearch]
})(({ theme }) => ({
  padding: theme.spacing(2, 2, 1, 2),
  '& input::placeholder, .ico': {
    color: theme.palette.text.hint
  }
}));

const HeaderActionStyled = styled('div')(({ theme }) => ({}));

const Content = styled('div', {
  name: 'Chat',
  slot: 'contentChat',
  overridesResolver: (props, styles) => [styles.contentChat]
})(({ theme }) => ({
  backgroundColor: theme.palette.background.paper,
  padding: theme.spacing(1, 0, 1, 1),
  flex: 1,
  minHeight: 0
}));

export default function Base({ title }: Props) {
  const { ListView, jsxBackend, i18n } = useGlobal();
  const scrollRef = React.useRef();

  const dataSource = {
    apiUrl: '/chat-room'
  };
  const [query, setQuery] = useState('');

  const onQueryChange = value => {
    setQuery(value);
  };

  return (
    <Root>
      <WrapperHeader data-testid={camelCase('_chat block header')}>
        <HeaderStyled>
          <Typography
            data-testid={camelCase('_chat block header title')}
            component="h1"
            variant="h3"
            color="textPrimary"
          >
            {i18n.formatMessage({ id: title || 'message' })}
          </Typography>
          <HeaderActionStyled
            data-testid={camelCase('_chat block header add ')}
          >
            {jsxBackend.render({
              component: 'chat.buttonAddChatRoom'
            })}
          </HeaderActionStyled>
        </HeaderStyled>
        <BlockSearchStyled>
          <SearchBox
            placeholder={i18n.formatMessage({ id: 'search_people' })}
            value={query}
            onQueryChange={onQueryChange}
          />
        </BlockSearchStyled>
      </WrapperHeader>
      <Content data-testid={camelCase('_chat block content')}>
        <ScrollContainer
          autoHide
          autoHeight
          autoHeightMax={'100%'}
          ref={scrollRef}
        >
          <ListView
            acceptQuerySearch
            query={query}
            dataSource={dataSource}
            canLoadMore
            canLoadSmooth
            numberOfItemsPerPage={20}
            gridContainerProps={{ rowSpacing: 0.5 }}
            gridLayout="ChatRoom - Main Card"
            blockLayout="Chat buddy list"
            itemLayout="ChatRoom - Main Card"
            itemView="blocked.itemView.chatroomCard"
            emptyPage="core.block.no_content"
            emptyPageProps={{
              title: query ? 'no_people_are_found' : 'no_messages',
              variant: 'center'
            }}
          />
        </ScrollContainer>
      </Content>
    </Root>
  );
}

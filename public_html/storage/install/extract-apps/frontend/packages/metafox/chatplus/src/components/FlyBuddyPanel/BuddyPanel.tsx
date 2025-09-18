import { useSessionUser } from '@metafox/chatplus/hooks';
import { getOnlineFriendsSelector } from '@metafox/chatplus/selectors';
import { ChatplusConfig, UserShape } from '@metafox/chatplus/types';
import { GlobalState, ListViewBlockProps, useGlobal } from '@metafox/framework';
import {
  Block,
  BlockContent,
  BlockHeader,
  BlockTitle,
  ScrollContainer
} from '@metafox/layout';
import { SearchBox } from '@metafox/ui';
import { styled, Box } from '@mui/material';
import { isEmpty } from 'lodash';
import React from 'react';
import { useSelector } from 'react-redux';
import 'slick-carousel/slick/slick-theme.css';
import 'slick-carousel/slick/slick.css';
import { PanelToolbar } from '../DockPanel';
import { actionsOnlineFriend } from './actions';
import OnlineList from './OnlineList';
import SkeletonItem from './SkeletonItem';

export type Props = ListViewBlockProps;

const InputSearch = styled('div', {
  name: 'LayoutSlot',
  slot: 'inputSearch',
  overridesResolver(props, styles) {
    return [styles.inputSearch];
  }
})(({ theme }) => ({
  padding: theme.spacing(2, 1, 1, 1)
}));

const SearchControl = styled('div', {
  name: 'SearchControl'
})(({ theme }) => ({
  display: 'flex',
  flexDirection: 'row'
}));

export default function BuddyPanel({ title, themeId }: Props) {
  const { i18n, useActionControl, getSetting, useLoggedIn } = useGlobal();
  const setting = getSetting<ChatplusConfig>('chatplus');
  const loggedIn = useLoggedIn();
  const userSession = useSessionUser();

  const [searchValueOnline, setSearchValueOnline] = React.useState<string>('');

  const [handleAction] = useActionControl<unknown, unknown>(null, {});

  const onlineFriends = useSelector<GlobalState, UserShape[]>(state =>
    getOnlineFriendsSelector(state, searchValueOnline)
  );

  const itemsActionOnline = actionsOnlineFriend();

  const onSearchOnlineInputChanged = evt => {
    if (evt && evt.target) {
      setSearchValueOnline(evt.target.value);
    }
  };

  if (!loggedIn || !setting || !setting.server) return null;

  return (
    <Block>
      <BlockHeader>
        <BlockTitle>{i18n.formatMessage({ id: title })}</BlockTitle>
        <Box>
          <PanelToolbar
            items={itemsActionOnline}
            handleAction={handleAction}
            popperOptions={{
              strategy: 'fixed'
            }}
          />
        </Box>
      </BlockHeader>
      <BlockContent style={{ height: '100%' }}>
        <ScrollContainer autoHeightMax={'100%'} autoHide autoHeight>
          {isEmpty(userSession) ? (
            <SkeletonItem />
          ) : (
            <Box>
              {isEmpty(onlineFriends) && !searchValueOnline ? null : (
                <InputSearch>
                  <SearchControl>
                    <SearchBox
                      placeholder={i18n.formatMessage({ id: 'search_friends' })}
                      value={searchValueOnline}
                      onChange={onSearchOnlineInputChanged}
                      style={{ height: '35px' }}
                    />
                  </SearchControl>
                </InputSearch>
              )}
              <OnlineList
                data={onlineFriends}
                searchValue={searchValueOnline}
              />
            </Box>
          )}
        </ScrollContainer>
      </BlockContent>
    </Block>
  );
}

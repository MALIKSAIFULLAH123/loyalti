import {
  getPagingSelector,
  GlobalState,
  initPagingState,
  ListViewBlockProps,
  PagingState,
  useGlobal,
  useGetItems,
  withPagination
} from '@metafox/framework';
import { Block, BlockContent } from '@metafox/layout';
// layout
import {
  Box,
  styled,
  Typography,
  Skeleton as SkeletonDefault
} from '@mui/material';
// components
import { range } from 'lodash';
import React from 'react';
import { useSelector } from 'react-redux';

const TitleSession = styled(Typography, { name: 'title' })(({ theme }) => ({
  marginBottom: theme.spacing(2),
  padding: theme.spacing(0, 2)
}));

const SkeletonSession = styled(SkeletonDefault, { name: 'SkeletonSession' })(
  ({ theme }) => ({
    margin: theme.spacing(1, 2),
    marginBottom: theme.spacing(2)
  })
);

const Session = styled(Box, { name: 'session' })(({ theme }) => ({
  marginBottom: theme.spacing(1),
  marginTop: theme.spacing(1.5)
}));

function ListView({
  itemView,
  itemProps = {},
  gridItemProps = {},
  pagingId
}: ListViewBlockProps) {
  const { jsxBackend, i18n, useSession, navigate } = useGlobal();

  const { user: authUser } = useSession();

  const ItemView = jsxBackend.get(itemView);
  const Skeleton = jsxBackend.get(`${itemView}.skeleton`);
  const OwnerCardView = jsxBackend.get('story.itemView.ownerCard');

  const paging =
    useSelector<GlobalState, PagingState>((state: GlobalState) =>
      getPagingSelector(state, pagingId)
    ) || initPagingState();

  const { error, initialized } = paging ?? {};

  const listUserPaging = useGetItems(paging?.ids);

  const ownerStory = listUserPaging.find(({ id }) => authUser?.id === id);

  const handleClickItem = () => {
    if (!ownerStory?.id) return;

    navigate('/story', { state: { related_user_id: ownerStory?.id } });
  };

  if (!ItemView) return null;

  if (!initialized && !error) {
    return (
      <Block>
        <BlockContent>
          <Session>
            <SkeletonSession height={20} width={'25%'} />
            {range(0, 1).map(index => (
              <Skeleton itemProps={itemProps} key={index.toString()} />
            ))}
          </Session>
        </BlockContent>
      </Block>
    );
  }

  return (
    <Block>
      <BlockContent>
        <Session>
          <TitleSession variant="h5">
            {i18n.formatMessage({ id: 'my_story' })}
          </TitleSession>
          {!error && ownerStory ? (
            <Box onClick={handleClickItem}>
              <ItemView identity={ownerStory._identity} itemProps={itemProps} />
            </Box>
          ) : (
            <OwnerCardView />
          )}
        </Session>
      </BlockContent>
    </Block>
  );
}

export default withPagination(ListView);

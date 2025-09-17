import { useGlobal } from '@metafox/framework';
import { Box } from '@mui/material';
import * as React from 'react';
import { CommentDivider } from '../Components';

const SORT_NEWEST = 'newest';

const SORT_OLDEST = 'oldest';

const FeedCommetList = ({
  identity,
  item,
  actions,
  handleAction,
  state,
  parent_user,
  isDetailFeed = false,
  handleClickComposer
}: Record<string, any>) => {
  const { CommentList, jsxBackend, useSession, useIsMobile, useSortComment } =
    useGlobal();
  const session = useSession();
  const isMobile = useIsMobile();

  if (!useSortComment || !CommentList) return null;

  // eslint-disable-next-line react-hooks/rules-of-hooks
  const [sortType, setSortType, loadingSort, setLoadingSort] = useSortComment();

  const CommentComposer = jsxBackend.get('CommentComposer');

  const { statistic } = item;

  const showCommentList = !isMobile || isDetailFeed;

  return [SORT_OLDEST, SORT_NEWEST].includes(sortType) ? (
    <>
      {session.loggedIn &&
      item.extra.can_comment &&
      CommentComposer &&
      !loadingSort &&
      showCommentList ? (
        <Box onClickCapture={handleClickComposer}>
          {React.createElement(CommentComposer, {
            identity,
            parentUser: parent_user,
            open: state.commentOpened,
            focus: state.commentFocused
          })}
        </Box>
      ) : null}
      {showCommentList && CommentList && (
        <CommentList
          identity={identity}
          handleAction={handleAction}
          open={state.commentOpened}
          data={item.related_comments}
          viewMoreComments={actions.viewMoreComments}
          total_comment={statistic?.total_comment}
          total_reply={statistic?.total_reply}
          parent_user={parent_user}
          sortType={sortType}
          setSortType={setSortType}
          setLoadingSort={setLoadingSort}
        />
      )}
    </>
  ) : (
    <>
      {showCommentList && CommentList && (
        <CommentList
          identity={identity}
          handleAction={handleAction}
          open={state.commentOpened}
          data={item.related_comments}
          viewMoreComments={actions.viewMoreComments}
          total_comment={statistic?.total_comment}
          total_reply={statistic?.total_reply}
          parent_user={parent_user}
          sortType={sortType}
          setSortType={setSortType}
          setLoadingSort={setLoadingSort}
          isDetailPage
        />
      )}
      {!loadingSort ? (
        <>
          {item?.related_comments?.length > 0 &&
          state.commentOpened &&
          showCommentList ? (
            <CommentDivider />
          ) : null}
          {session.loggedIn &&
          item.extra.can_comment &&
          CommentComposer &&
          showCommentList ? (
            <div onClickCapture={handleClickComposer}>
              {React.createElement(CommentComposer, {
                identity,
                parentUser: parent_user,
                open: state.commentOpened,
                focus: state.commentFocused
              })}
            </div>
          ) : null}
        </>
      ) : null}
    </>
  );
};

export default FeedCommetList;

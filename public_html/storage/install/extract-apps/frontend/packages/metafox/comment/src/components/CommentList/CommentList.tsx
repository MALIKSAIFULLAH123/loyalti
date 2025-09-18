/**
 * @type: service
 * name: CommentList
 */
import { getItemSelector, GlobalState, useGlobal } from '@metafox/framework';
import { ItemShape } from '@metafox/ui';
import { styled, Box, CircularProgress } from '@mui/material';
import React from 'react';
import { useSelector } from 'react-redux';
import { CommentListProps } from '../../types';
import PreFetchComment from '../Comment/PreFetchComment';
import {
  SORT_ALL,
  SortTypeValue,
  SORT_NEWEST,
  getValueSortTypeMode,
  getListingDataBySortType,
  COMMENT_PAGINATION_CLEAR,
  COMMENT_ACTION_INIT,
  COMMENT_ACTION_CHANGE_SORT,
  SORT_RELEVANT,
  COMMENT_PAGINATION_PREFIX
} from '@metafox/comment';
import { uniqBy, assign, isEmpty } from 'lodash';

const CommentListRoot = styled('div', {
  name: 'CommentList',
  slot: 'Root',
  overridesResolver(props, styles) {
    return [styles.root];
  }
})(({ theme }) => ({
  position: 'relative',
  '&:last-child': {
    paddingBottom: theme.spacing(1)
  },
  '& $CommentRoot:first-of-type': {
    marginTop: theme.spacing(1)
  },
  '& $CommentRoot:last-child': {
    borderBottom: 'solid 1px',
    borderBottomColor: theme.palette.border?.secondary,
    paddingBottom: theme.spacing(2),
    [theme.breakpoints.down('sm')]: {
      borderBottom: 'none'
    }
  }
}));

const CommentRoot = styled('div', {
  name: 'CommentList',
  slot: 'Stage',
  overridesResolver(props, styles) {
    return [styles.stage];
  }
})(({ theme }) => ({
  position: 'relative',
  '&:before': {
    content: '""',
    position: 'absolute',
    top: theme.spacing(6),
    bottom: theme.spacing(2),
    left: theme.spacing(2),
    borderLeft: 'solid 1px',
    borderLeftColor: theme.palette.border?.secondary
  }
}));

const ViewMoreComment = styled(Box, { name: 'viewMoreComment' })(
  ({ theme }) => ({
    color: theme.palette.primary.main,
    '&:hover': {
      textDecoration: 'underline'
    }
  })
);

export default function CommentList({
  identity,
  data: dataDefault,
  open = true,
  total_comment,
  total_reply = 0,
  viewMoreComments,
  parent_user,
  sortType,
  setSortType,
  handleAction,
  searchParams,
  setLoadingSort: setLoadingSortParent,
  isDetailPage = false,
  disablePortalSort = true,
  showActionMenu = true
}: CommentListProps) {
  const {
    CommentItemView,
    i18n,
    getSetting,
    useGetItem,
    useGetItems,
    SortCommentList,
    useSession,
    usePageParams,
    usePrevious
  } = useGlobal();
  const sortTypeSetting: SortTypeValue = getSetting('comment.sort_by');
  const pageParams = usePageParams();
  const { comment_id } = assign(searchParams, pageParams);
  const prevCommentId = usePrevious(comment_id);
  const [loadingSort, setLoadingSort] = React.useState(false);
  const [loadingMore, setLoadingMore] = React.useState(false);
  const sortTypeMode = getValueSortTypeMode(sortType);
  const pagingId = `${COMMENT_PAGINATION_PREFIX}${sortType}/${identity.replace(
    /\./g,
    '_'
  )}/${comment_id}`;
  const pagingData = useGetItem(`pagination.${pagingId}`);
  const session = useSession();
  const init = React.useRef(false);

  const item = useSelector<GlobalState>(state =>
    getItemSelector(state, identity)
  ) as ItemShape & {
    preFetchingComment: Record<string, any>;
    excludesComment: string[];
    commentsNew: string[];
    relevant_comments: string[];
  };
  const {
    preFetchingComment,
    commentsNew = [],
    excludesComment = [],
    relevant_comments = []
  } = item || {};

  const { ended: endedMore } = pagingData || {};
  const isLoadingRelevant =
    sortType === SORT_RELEVANT && isEmpty(relevant_comments);

  const idsFull = getListingDataBySortType(pagingData, item, sortType);
  const dataComments = useGetItems([...commentsNew, ...idsFull]);

  const dataSort = uniqBy(dataComments, 'id');
  const countCommentShowed = dataSort ? dataSort.length : 0;
  const remainComment = Math.max(
    Math.min(10, total_comment - total_reply - countCommentShowed),
    0
  );

  const isShowSort = idsFull.length > 1 || !!remainComment;

  React.useEffect(() => {
    setLoadingMore(false);
  }, [dataComments.length]);

  React.useEffect(() => {
    handleAction(
      COMMENT_ACTION_INIT,
      { sortTypeInit: sortTypeSetting },
      {
        onSuccess: () => {
          init.current = true;
        }
      }
    );
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [identity]);

  React.useEffect(() => {
    if (!init.current || !prevCommentId || !comment_id) return;

    handleAction(COMMENT_PAGINATION_CLEAR, {
      clearEntity: `comment.entities.comment.${prevCommentId}`
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [comment_id]);

  React.useEffect(() => {
    handleAction(
      COMMENT_ACTION_CHANGE_SORT,
      { sortType, excludes: excludesComment, pagingId },
      {
        onSuccess: () => setLoadingSort(false),
        onStart: () => setLoadingSort(true)
      }
    );
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [sortTypeMode]);

  React.useEffect(() => {
    if (setLoadingSortParent) {
      setLoadingSortParent(loadingSort);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [loadingSort]);

  if (!open || !item?.extra?.can_view_comment) return null;

  if (total_comment === 0 && !preFetchingComment) return null;

  const hasComment = dataSort && dataSort.length;
  const showViewMore = 0 < remainComment && !endedMore;

  const handleClickMore = () => {
    setLoadingMore(true);
    viewMoreComments(
      {
        sortType,
        pagingId
      },
      {
        onSuccess: () => {
          setLoadingMore(false);
        }
      }
    );
  };

  if (loadingSort || isLoadingRelevant) {
    return (
      <CommentListRoot data-testid="listCommentItems">
        {isShowSort ? (
          <Box pt={1}>
            <SortCommentList value={sortType} setValue={setSortType} />
          </Box>
        ) : null}
        <Box
          p={2}
          sx={{
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center'
          }}
        >
          <CircularProgress size={16} />
        </Box>
      </CommentListRoot>
    );
  }

  return (
    <CommentListRoot data-testid="listCommentItems">
      {isShowSort ? (
        <Box
          pt={
            isDetailPage || sortType === SORT_ALL || !session.loggedIn ? 1 : 0
          }
        >
          <SortCommentList
            value={sortType}
            setValue={setSortType}
            disablePortal={disablePortalSort}
          />
        </Box>
      ) : null}
      <Box
        sx={{
          display: 'flex',
          flexDirection: sortType === SORT_ALL ? 'column-reverse' : 'column'
        }}
      >
        {preFetchingComment
          ? Object.values(preFetchingComment)
              .filter(item => item?.isLoading === true)
              .reverse()
              .map((item, index) => (
                <CommentRoot key={item?.id ?? `id${index}`}>
                  <PreFetchComment text={item.text} />
                </CommentRoot>
              ))
          : null}
        {hasComment ? (
          <Box
            sx={{
              display: 'flex',
              flexDirection: sortType === SORT_ALL ? 'column-reverse' : 'column'
            }}
          >
            {dataSort.map((item, index) => (
              <CommentRoot key={item?.id ?? `id${index}`}>
                <CommentItemView
                  identity={`comment.entities.comment.${item?.id}`}
                  identityResource={identity}
                  parent_user={parent_user}
                  searchParams={searchParams}
                  sortType={sortType}
                  showActionMenu={showActionMenu}
                />
              </CommentRoot>
            ))}
          </Box>
        ) : null}
        {showViewMore && (
          <ViewMoreComment my={1}>
            <Box
              sx={{ display: 'flex', alignItems: 'center' }}
              role="button"
              onClick={handleClickMore}
            >
              {i18n.formatMessage(
                {
                  id: [SORT_ALL, SORT_NEWEST].includes(sortType)
                    ? 'view_previous_comment'
                    : 'view_more_comment'
                },
                { value: remainComment }
              )}
              {loadingMore && (
                <CircularProgress sx={{ marginLeft: '4px' }} size={12} />
              )}
            </Box>
          </ViewMoreComment>
        )}
      </Box>
    </CommentListRoot>
  );
}

import { useGlobal, HandleAction } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { Box, styled, CircularProgress } from '@mui/material';
import React from 'react';
import PreFetchComment from '../Comment/PreFetchComment';
import {
  SortTypeValue,
  SORT_OLDEST,
  SORT_RELEVANT,
  SORT_MODE_ASC,
  SORT_MODE_DESC,
  COMMENT_PAGINATION_PREFIX
} from '@metafox/comment';
import {
  getListingReplyDataBySortType,
  getValueSortTypeMode
} from '@metafox/comment/utils';
import { last, first, get, isEmpty, uniqBy } from 'lodash';

const name = 'Comment';

const ReplyListing = styled('div', { name, slot: 'ReplyListing' })(
  ({ theme }) => ({
    paddingLeft: theme.spacing(5),
    '& > div': {
      position: 'relative',
      '&:before': {
        content: '""',
        width: 12,
        borderTop: 'solid 1px',
        borderTopColor: theme.palette.border?.secondary,
        position: 'absolute',
        left: theme.spacing(-3),
        top: theme.spacing(3)
      }
    }
  })
);

const ViewMoreReplyButton = styled('div', { name, slot: 'AvatarWrapper' })(
  ({ theme }) => ({
    paddingLeft: theme.spacing(10),
    paddingTop: theme.spacing(1),
    paddingBottom: theme.spacing(1),
    color: theme.palette.primary.main,
    display: 'flex',
    alignItems: 'center',
    '&:hover': {
      textDecoration: 'underline'
    }
  })
);

const Reply = styled(Box, { name, slot: 'Reply' })(({ theme }) => ({
  alignItems: 'baseline'
}));

const compareDate = (a, b, sortKeySetting) => {
  return (
    new Date(a[sortKeySetting]).valueOf() -
    new Date(b[sortKeySetting]).valueOf()
  );
};

type Props = {
  parent_user?: Record<string, any>;
  actions?: Record<string, any>;
  handleAction?: HandleAction;
  identity?: string;
  sortType: SortTypeValue;
  comment_id?: string;
};

export default function ReplyCommentList({
  identity,
  actions,
  parent_user,
  handleAction,
  sortType: sortTypeParent,
  comment_id
}: Props) {
  const [loadingMore, setLoadingMore] = React.useState('');
  const { i18n, ReplyItemView, getSetting, useGetItems, useGetItem } =
    useGlobal();
  const item = useGetItem(identity);
  const {
    reply_detail_statistics,
    relevant_children,
    commentsNew = [],
    statistic
  } = item;
  const { total_comment = 0 } = statistic || {};

  const sortTypeDefault: SortTypeValue = getSetting('comment.sort_reply_by');
  const isDetailCommentMode =
    sortTypeParent === SORT_RELEVANT && !isEmpty(relevant_children);
  const sortType = isDetailCommentMode ? SORT_RELEVANT : sortTypeDefault;
  const sortKeySetting = getSetting('comment.sort_by_key') || 'creation_date';

  const pagingId = `${COMMENT_PAGINATION_PREFIX}${sortType}/${identity.replace(
    /\./g,
    '_'
  )}/${comment_id}`;
  const pagingData = useGetItem(`pagination.${pagingId}`);
  const { ended: endedMore } = pagingData || {};
  const ids = getListingReplyDataBySortType(pagingData, item, sortType);
  const dataChildren = useGetItems(ids);
  const dataNews = useGetItems(commentsNew);

  const { total_more_remaining, total_previous_remaining } =
    reply_detail_statistics || {};

  if (!item) return null;

  const { is_hidden } = item;

  const preFetchingComment = Object.values(
    item?.preFetchingComment || {}
  ).filter(item => item?.isLoading === true);

  const handleClickMore = (id, mode) => {
    setLoadingMore(mode);
    actions.viewMoreReplies(
      { pagingId, sortType, lastId: id, viewmoreSortTypeMode: mode },
      { onSuccess: () => setLoadingMore('false') }
    );
  };

  const dataSort = dataChildren.sort((a, b) =>
    compareDate(a, b, sortKeySetting)
  );
  // except newComments when get lastId and firstId
  const lastId = get(last(dataSort), 'id');
  const firstId = get(first(dataSort), 'id');
  const finalChildren = uniqBy(
    [...dataSort, ...dataNews].filter(x => !!x),
    'id'
  );
  const sortTypeMode = getValueSortTypeMode(sortType);
  const remainChild = Math.min(
    total_comment - (finalChildren?.length || 0),
    10
  );

  if (!(total_comment > 0) && !(preFetchingComment?.length > 0)) {
    return null;
  }

  return (
    <Box
      sx={{
        display: 'flex',
        flexDirection: [SORT_OLDEST, SORT_RELEVANT].includes(sortType)
          ? 'column'
          : 'column-reverse'
      }}
    >
      {total_previous_remaining > 0 && isDetailCommentMode ? (
        <ViewMoreReplyButton
          aria-label="reply"
          role="button"
          onClick={() => handleClickMore(firstId, SORT_MODE_DESC)}
        >
          <span>
            {i18n.formatMessage(
              {
                id: 'view_previous_reply'
              },
              { value: total_previous_remaining }
            )}
          </span>
          {loadingMore === SORT_MODE_DESC && (
            <CircularProgress sx={{ marginLeft: '4px' }} size={12} />
          )}
        </ViewMoreReplyButton>
      ) : null}
      {!is_hidden && (finalChildren?.length || preFetchingComment?.length) ? (
        <ReplyListing>
          {finalChildren?.length
            ? finalChildren.map(item => (
                <ReplyItemView
                  identity={`comment.entities.comment.${item.id}`}
                  openReplyComposer={actions.openReplyComposer}
                  key={`${item?.id}`}
                  parent_user={parent_user}
                />
              ))
            : null}
          {preFetchingComment?.length
            ? preFetchingComment?.map(item => (
                <PreFetchComment key={item.key} text={item.text} />
              ))
            : null}
        </ReplyListing>
      ) : null}
      {total_more_remaining > 0 && isDetailCommentMode ? (
        <ViewMoreReplyButton
          aria-label="reply"
          role="button"
          onClick={() => handleClickMore(lastId, SORT_MODE_ASC)}
        >
          <span>
            {i18n.formatMessage(
              {
                id: 'view_more_reply'
              },
              { value: total_more_remaining }
            )}
          </span>
          {loadingMore === SORT_MODE_ASC && (
            <CircularProgress sx={{ marginLeft: '4px' }} size={12} />
          )}
        </ViewMoreReplyButton>
      ) : null}
      {!is_hidden && 0 < remainChild && !endedMore && !isDetailCommentMode ? (
        <ViewMoreReplyButton
          aria-label="reply"
          role="button"
          onClick={() =>
            handleClickMore(
              sortTypeMode === SORT_MODE_ASC ? lastId : firstId,
              sortTypeMode
            )
          }
        >
          {finalChildren?.length ? (
            <span>
              {i18n.formatMessage(
                {
                  id:
                    sortType === SORT_OLDEST
                      ? 'view_more_reply'
                      : 'view_previous_reply'
                },
                { value: remainChild }
              )}
            </span>
          ) : (
            <Reply>
              <LineIcon icon="ico-forward" />{' '}
              <span>
                {i18n.formatMessage(
                  { id: 'number_reply' },
                  { value: total_comment }
                )}
              </span>
            </Reply>
          )}
          {loadingMore === sortTypeMode && (
            <CircularProgress sx={{ marginLeft: '4px' }} size={12} />
          )}
        </ViewMoreReplyButton>
      ) : null}
    </Box>
  );
}

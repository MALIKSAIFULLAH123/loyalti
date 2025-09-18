import { useGlobal } from '@metafox/framework';
import { styled } from '@mui/material';
import React from 'react';
import PreFetchComment from './Comment/PreFetchComment';

const CommentListRoot = styled('div', { name: 'CommentListRoot' })(
  ({ theme }) => ({
    position: 'relative',
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
  })
);

const CommentRoot = styled('div', { name: 'CommentListRoot' })(({ theme }) => ({
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

export default function CommentList({
  identity,
  data: dataDefault,
  scrollToBottom,
  setParentReply
}) {
  const { useGetItem, jsxBackend } = useGlobal();
  const CommentItemViewLiveStreaming = jsxBackend.get(
    'CommentItemViewLiveStreaming'
  );
  const item = useGetItem(identity);
  const { preFetchingComment } = item || {};
  const hasPreFetching =
    preFetchingComment &&
    Object.values(preFetchingComment).some(item => item?.isLoading === true);

  React.useEffect(() => {
    if (scrollToBottom) {
      scrollToBottom();
    }
  }, [hasPreFetching, scrollToBottom]);

  if (!CommentItemViewLiveStreaming) return null;

  return (
    <CommentListRoot>
      {dataDefault?.length
        ? dataDefault.map(item => (
            <CommentRoot key={item?.id}>
              <CommentItemViewLiveStreaming
                itemLive={item}
                identity={`comment.entities.comment.${item?.id}`}
                setParentReply={setParentReply}
              />
            </CommentRoot>
          ))
        : null}
      {preFetchingComment
        ? Object.values(preFetchingComment)
            .filter(item => item?.isLoading === true)
            .map(item => (
              <CommentRoot key={item}>
                <PreFetchComment text={item.text} />
              </CommentRoot>
            ))
        : null}
    </CommentListRoot>
  );
}

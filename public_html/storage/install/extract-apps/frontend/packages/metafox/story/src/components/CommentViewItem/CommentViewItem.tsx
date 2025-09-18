import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { Box, IconButton, Typography, styled } from '@mui/material';
import React from 'react';
import CommentList from '@metafox/core/components/CommentList';
import { ScrollContainer } from '@metafox/layout';
import { isEmpty } from 'lodash';
import WapperItemInteraction from '../WapperItemInteraction';

const name = 'CommentViewItem';

const HeaderBlock = styled(Box, { name, slot: 'HeaderBlock' })(({ theme }) => ({
  padding: theme.spacing(2),
  paddingTop: 0,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  borderBottom: theme.mixins.border('secondary')
}));
const HeaderTitle = styled(Box, { name, slot: 'HeaderTitle' })(() => ({}));

const CloseButton = styled(IconButton, { name })(() => ({
  marginLeft: 'auto',
  transform: 'translate(4px,0)',
  position: 'absolute',
  right: '16px'
}));

interface Props {
  setOpen: any;
  open: boolean;
  identity: any;
  isMinHeight?: boolean;
}

const CommentViewItem = ({
  setOpen,
  open,
  identity,
  isMinHeight = false
}: Props) => {
  const { i18n, jsxBackend, useActionControl, useGetItem } = useGlobal();

  const commentInputRef = React.useRef();
  const [handleAction] = useActionControl(identity, {
    commentFocused: false,
    menuOpened: false,
    commentOpened: true,
    commentInputRef
  });
  const story = useGetItem(identity);

  if (isEmpty(story)) return null;

  const { related_comments, statistic, extra, is_owner } = story || {};

  const preFetchingComment = Object.values(
    story?.preFetchingComment || {}
  ).filter(item => item?.isLoading === true);

  const CommentComposer = jsxBackend.get('CommentComposer');

  const handleClose = () => {
    setOpen(false);
  };

  const viewMoreComments = (payload, meta) =>
    handleAction('comment/viewMoreComments', payload, meta);

  const onSuccess = () => {};

  return (
    <WapperItemInteraction
      setOpen={setOpen}
      open={open}
      isMinHeight={isMinHeight}
    >
      <HeaderBlock>
        <HeaderTitle>
          <Typography variant="h4" color={'text.primary'}>
            {i18n.formatMessage({ id: 'comments' })}
          </Typography>
        </HeaderTitle>
        <CloseButton
          size="small"
          onClick={handleClose}
          data-testid="buttonClose"
          role="button"
        >
          <LineIcon icon="ico-close" />
        </CloseButton>
      </HeaderBlock>

      <Box sx={{ flex: 1, minHeight: 0 }}>
        {!statistic?.total_comment && !preFetchingComment?.length ? (
          <Typography
            variant="body1"
            color={'text.secondary'}
            sx={{ mt: 1, paddingX: 2 }}
          >
            {i18n.formatMessage(
              { id: 'description_no_comment_story' },
              { is_owner: is_owner ? 1 : 0 }
            )}
          </Typography>
        ) : (
          CommentList && (
            <ScrollContainer autoHide autoHeight autoHeightMax={'100%'}>
              <Box px={2}>
                <CommentList
                  id-tid="comment_list"
                  handleAction={handleAction}
                  data={related_comments}
                  viewMoreComments={viewMoreComments}
                  total_comment={statistic?.total_comment}
                  total_reply={statistic?.total_reply}
                  identity={identity}
                  open
                  isDetailPage
                  disablePortalSort={false}
                />
              </Box>
            </ScrollContainer>
          )
        )}
      </Box>
      {extra?.can_comment && CommentComposer ? (
        <Box px={2}>
          <CommentComposer
            id-tid="comment_composer_story"
            identity={identity}
            open
            focus
            ref={commentInputRef}
            onSuccess={onSuccess}
          />
        </Box>
      ) : null}
    </WapperItemInteraction>
  );
};

export default CommentViewItem;

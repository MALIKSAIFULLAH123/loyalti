import { connectItem, useGlobal } from '@metafox/framework';
import {
  Block,
  BlockContent,
  BlockHeader,
  ScrollContainer
} from '@metafox/layout';
import * as React from 'react';
import { LivestreamDetailViewProps } from '../../types';
import CommentListLiveStream from './CommentContainer';
import { Box } from '@mui/material';

function LivestreamComment({
  item,
  identity,
  handleAction,
  state,
  title
}: LivestreamDetailViewProps) {
  const { i18n, jsxBackend, getSetting } = useGlobal();
  const enableCommentApp = getSetting('comment');
  const CommentComposer = jsxBackend.get('CommentComposer');
  const ReplyInfo = jsxBackend.get('livestreaming.ui.composerReplyInfo');
  const [parentReply, setParentReply] = React.useState<
    Record<string, any> | undefined
  >();
  const [focusState, setFocusState] = React.useState<number>(0);
  const scrollRef = React.useRef();

  if (!item || !enableCommentApp) return null;

  const { extra } = item;

  const scrollToBottom = () => {
    if (!scrollRef?.current) return;

    const yOffset = 0;
    const y = scrollRef.current?.scrollHeight + yOffset;

    scrollRef.current?.scrollTo({ top: y });
  };

  const handleSuccess = () => {
    setParentReply(undefined);
  };

  const removeReply = () => {
    setParentReply(undefined);
  };

  const handleReply = data => {
    setParentReply(data);
    setFocusState(prev => prev + 1);
  };

  return (
    <Block testid={`detailview ${item.resource_name}`}>
      <BlockHeader title={i18n.formatMessage({ id: title })}></BlockHeader>
      <BlockContent>
        <Box sx={{ display: 'flex', flexDirection: 'column', height: '100%' }}>
          <Box sx={{ flex: 1, minHeight: 0 }}>
            <ScrollContainer
              autoHide
              autoHeight
              autoHeightMax={'100%'}
              ref={scrollRef}
            >
              <Box px={2} pb={2}>
                <CommentListLiveStream
                  identity={identity}
                  scrollToBottom={scrollToBottom}
                  streamKey={item?.stream_key}
                  setParentReply={handleReply}
                />
              </Box>
            </ScrollContainer>
          </Box>
          {extra?.can_comment ? (
            <Box px={2}>
              {parentReply ? (
                <ReplyInfo item={parentReply} removeReply={removeReply} />
              ) : null}
              <CommentComposer
                open
                identity={parentReply?.parent_comment_identity || identity}
                onSuccess={handleSuccess}
                editorConfig={{ disable_photo: true }}
                focus={focusState}
              />
            </Box>
          ) : null}
        </Box>
      </BlockContent>
    </Block>
  );
}

export default connectItem(LivestreamComment);

/**
 * @type: block
 * name: livestreaming.block.commentLiveListing
 * title: livestream detail comment
 * keywords: livestreaming
 * experiment: true
 * experiment: true
 */
import { connectItem, useGlobal } from '@metafox/framework';
import * as React from 'react';
import CommentListLiveStream from './CommentList';
import {
  useFirestoreDocIdListener,
  useFirebaseFireStore
} from '@metafox/framework/firebase';

type Props = {
  streamKey: string;
  identity: string;
  scrollToBottom: () => void;
  setParentReply: () => void;
};

function LivestreamComment({
  streamKey,
  identity,
  scrollToBottom,
  setParentReply
}: Props) {
  const { dispatch, useGetItem, getSetting } = useGlobal();
  const item = useGetItem(identity);
  const enableCommentApp = getSetting('comment');

  const db = useFirebaseFireStore();
  const obsConnected = useFirestoreDocIdListener(db, {
    collection: 'live_video_comment',
    docID: streamKey
  });

  const totalComment = obsConnected?.total_comment;

  React.useEffect(() => {
    if (item?.statistic?.total_comment === totalComment) return;

    dispatch({
      type: 'livestreaming/updateStatistic',
      payload: {
        identity,
        statistic: {
          total_comment: totalComment || item?.statistic?.total_comment
        }
      }
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [totalComment, item?.statistic?.total_comment]);

  React.useEffect(() => {
    dispatch({
      type: 'livestreaming/updateComment',
      payload: {
        data: obsConnected?.comment
      }
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [obsConnected?.comment]);

  if (!streamKey || !obsConnected) return null;

  const dataComment = obsConnected?.comment.slice(
    Math.max(obsConnected?.comment.length - 20, 0)
  );

  if (!enableCommentApp) return null;

  return (
    <CommentListLiveStream
      data={dataComment}
      identity={identity}
      scrollToBottom={scrollToBottom}
      setParentReply={setParentReply}
    />
  );
}

export default connectItem(LivestreamComment);

/**
 * @type: ui
 * name: statusComposer.control.StatusUploadVideo
 * chunkName: statusComposerControl
 */
import { StatusComposerControlProps, useGlobal } from '@metafox/framework';
import React from 'react';

export default function StatusAddPollButton(props: StatusComposerControlProps) {
  const { control: Control, disabled, composerRef } = props;
  const { i18n, dialogBackend } = useGlobal();
  const onClick = React.useCallback(() => {
    dialogBackend.present({
      component: 'video.dialog.AddVideoToStatusComposerDialog',
      props: {}
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [composerRef, dialogBackend]);

  return (
    <Control
      onClick={onClick}
      disabled={disabled}
      testid="buttonAttachVideo"
      title={i18n.formatMessage({
        id: 'video_url'
      })}
      label={i18n.formatMessage({ id: 'video_url' })}
      icon="ico-video-url"
    />
  );
}

/**
 * @type: ui
 * name: statusComposer.control.LiveStreamingButton
 * chunkName: statusComposerControl
 */
import { StatusComposerControlProps, useGlobal } from '@metafox/framework';
import React from 'react';

export default function LiveStreamingButtonComposer(
  props: StatusComposerControlProps & {
    label: string;
  }
) {
  const { i18n, usePageParams, compactUrl } = useGlobal();
  const { control: Control, disabled, label } = props;
  const { profile_id } = usePageParams();
  const to = profile_id
    ? '/live-video/add?owner_id=:profile_id'
    : '/live-video/add';

  return (
    <Control
      disabled={disabled}
      testid="livestreamingCreate"
      href={compactUrl(to, { profile_id })}
      icon="ico-videocam-o"
      label={label}
      title={i18n.formatMessage({
        id: 'live_video'
      })}
    />
  );
}

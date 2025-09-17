/**
 * @type: ui
 * name: statusComposer.control.FeedScheduleButton
 * chunkName: statusComposerControl
 */
import { StatusComposerControlProps, useGlobal } from '@metafox/framework';
import { get } from 'lodash';
import React from 'react';

export default function StatusCheckInButton({
  control: Control,
  disabled,
  composerRef
}: StatusComposerControlProps) {
  const { i18n, dispatch } = useGlobal();
  const { setScheduleTime, state } = composerRef.current;

  const onClick = () => {
    dispatch({
      type: 'statusComposer/openScheduleFeed',
      payload: {
        value: get(state, 'schedule_time.value'),
        setScheduleTime
      }
    });
  };

  return (
    <Control
      disabled={disabled}
      onClick={onClick}
      testid="buttonFeedSchedule"
      icon="ico-clock-o"
      label={i18n.formatMessage({ id: 'schedule' })}
      title={i18n.formatMessage({
        id: disabled ? 'this_cant_be_combined' : 'schedule'
      })}
    />
  );
}

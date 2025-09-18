/**
 * @type: ui
 * name: statusComposer.StatusAddPollButton
 */
import { StatusComposerControlProps, useGlobal } from '@metafox/framework';
import React from 'react';

export default function StatusAddPollButton(props: StatusComposerControlProps) {
  const { control: Control, disabled, composerRef } = props;
  const { i18n, dialogBackend } = useGlobal();
  const onClick = React.useCallback(() => {
    dialogBackend
      .present({
        component: 'poll.dialog.AddPollToStatusComposerDialog',
        props: {}
      })
      .then(value => {
        if (!value) return;

        composerRef.current.setAttachments('poll', 'poll', {
          value,
          as: 'StatusComposerControlAttachedPoll'
        });

        const { requestComposerUpdate } = composerRef.current;

        requestComposerUpdate && requestComposerUpdate();
      });
  }, [composerRef, dialogBackend]);

  return (
    <Control
      onClick={onClick}
      disabled={disabled}
      testid="buttonAttachPoll"
      title={i18n.formatMessage({
        id: disabled ? 'this_cant_be_combined' : 'create_poll'
      })}
      label={i18n.formatMessage({ id: 'poll' })}
      icon="ico-barchart-o"
    />
  );
}

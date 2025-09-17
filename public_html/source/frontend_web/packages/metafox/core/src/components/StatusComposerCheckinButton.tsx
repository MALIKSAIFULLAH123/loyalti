/**
 * @type: ui
 * name: statusComposer.control.CheckInButton
 * chunkName: statusComposerControl
 */
import {
  StatusComposerControlProps,
  useGlobal,
  CORE_GOOGLE_GOOGLE_MAP_API_KEY
} from '@metafox/framework';
import { get } from 'lodash';
import React from 'react';

export default function StatusCheckInButton({
  control: Control,
  disabled,
  composerRef
}: StatusComposerControlProps) {
  const { i18n, dialogBackend } = useGlobal();
  const { setTags } = composerRef.current;

  if (!CORE_GOOGLE_GOOGLE_MAP_API_KEY) return null;

  const onClick = () =>
    dialogBackend
      .present({
        component: 'core.dialog.PlacePickerDialog',
        props: {
          defaultValue: get(composerRef.current.state, 'tags.place.value')
        }
      })
      .then(value => {
        if (value === false) {
          setTags('place', {
            as: 'StatusComposerControlTaggedPlace',
            priority: 3,
            value: undefined
          });
        } else if (value) {
          setTags('place', {
            as: 'StatusComposerControlTaggedPlace',
            priority: 3,
            value
          });
        }
      });

  return (
    <Control
      disabled={disabled}
      onClick={onClick}
      testid="buttonAttachLocation"
      icon="ico-checkin-o"
      label={i18n.formatMessage({ id: 'checkin' })}
      title={i18n.formatMessage({
        id: disabled ? 'this_cant_be_combined' : 'checkin'
      })}
    />
  );
}

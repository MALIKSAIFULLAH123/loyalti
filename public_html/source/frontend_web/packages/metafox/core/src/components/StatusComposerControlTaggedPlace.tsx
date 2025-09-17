/**
 * @type: ui
 * name: StatusComposerControlTaggedPlace
 */
import { StatusComposerControlProps, useGlobal } from '@metafox/framework';
import { Box, styled } from '@mui/material';
import React from 'react';

const name = 'TaggedPlace';

const RootStyled = styled(Box, {
  name,
  slot: 'root',
  shouldForwardProp: props => props !== 'allowCheckIn'
})<{ allowCheckIn?: boolean }>(({ theme, allowCheckIn }) => ({
  cursor: allowCheckIn ? 'pointer' : 'auto',
  '&:not(:first-of-type)': {
    marginLeft: theme.spacing(0.5)
  }
}));

export default function StatusComposerControlTaggedPlace(
  props: StatusComposerControlProps
) {
  const { i18n, dialogBackend, getSetting } = useGlobal();
  const { value } = props;

  const allowCheckIn = getSetting('activity.feed.enable_check_in') as boolean;

  if (!value) {
    return null;
  }

  const onClick = () => {
    if (!allowCheckIn) return;

    dialogBackend
      .present({
        component: 'core.dialog.PlacePickerDialog',
        props: {
          defaultValue: value
        }
      })
      .then(value => {
        if (value === false) {
          const { setTags } = props.composerRef.current;

          setTags('place', {
            as: 'StatusComposerControlTaggedPlace',
            priority: 3,
            value: undefined
          });
        } else if (value) {
          const { setTags } = props.composerRef.current;

          setTags('place', {
            as: 'StatusComposerControlTaggedPlace',
            priority: 3,
            value
          });
        }
      });
  };

  return (
    <RootStyled
      component="span"
      onClick={onClick}
      data-place="tagPlace"
      allowCheckIn={allowCheckIn}
    >
      {i18n.formatMessage(
        { id: 'at_tagged_place' },
        {
          name: value.name || value.address
        }
      )}
    </RootStyled>
  );
}

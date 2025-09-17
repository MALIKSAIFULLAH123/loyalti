/**
 * @type: ui
 * name: feed.ui.addPhotoButton
 * chunkName: statusComposerControl
 */
import { CtaActionShape, useGlobal } from '@metafox/framework';
import { styled } from '@mui/material';
import { isEmpty } from 'lodash';
import * as React from 'react';
import useStatusComposer from '../../hooks/useStatusComposer';
import Control from './Control';

const strategy = 'block';

const StyledToolbar = styled('div')(({ theme }) => ({
  display: 'flex',
  margin: theme.spacing(0, 1.5)
}));

const AddPhotoButtonWrapper = styled('div')(({ theme }) => ({
  display: 'flex',
  flexDirection: 'row'
}));

export default function AddPhotoButton(headerActions: CtaActionShape) {
  const [composerState, , composerRef] = useStatusComposer();
  const { useSession, dispatch, jsxBackend, usePageParams } = useGlobal();
  const { user: authUser } = useSession();
  const { label } = headerActions;
  const pageParams = usePageParams();

  const { identity: parentIdentity, resource_name: parentType } = pageParams;

  composerRef.current.requestComposerUpdate = React.useCallback(() => {
    setImmediate(() => {
      const { attachmentType, attachments } = composerRef.current.state;

      dispatch({
        type: 'statusComposer/onPress/status',
        payload: {
          data: {
            attachmentType,
            attachments: {
              [attachmentType]: attachments[attachmentType]
            }
          },
          parentIdentity,
          parentType
        }
      });
    });
  }, [composerRef, dispatch, parentIdentity, parentType]);

  const handleResetRef = () => {
    composerRef.current.removeAttachments();
  };

  if (isEmpty(authUser)) return null;

  return (
    <AddPhotoButtonWrapper data-testid="addPhotoButton">
      <StyledToolbar onClick={handleResetRef}>
        {jsxBackend.render({
          component: 'statusComposer.control.StatusUploadPhotoButton',
          props: {
            strategy,
            composerRef,
            composerState,
            control: Control,
            label
          }
        })}
      </StyledToolbar>
    </AddPhotoButtonWrapper>
  );
}

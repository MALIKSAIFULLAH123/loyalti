/**
 * @type: ui
 * name: AttachPollPreview
 */

import { AttachPollPreviewProps, useGlobal } from '@metafox/framework';
import { ButtonList, TruncateText } from '@metafox/ui';
import { Box, Button, Typography } from '@mui/material';
import { styled } from '@mui/material/styles';
import React from 'react';
import moment from 'moment';
import { useFormikContext } from 'formik';

const PollWrapper = styled(Box, {
  name: 'StatusPoll',
  slot: 'Root'
})(({ theme }) => ({
  borderRadius: theme.shape.borderRadius,
  border: theme.mixins.border('secondary'),
  padding: theme.spacing(3)
}));

const Answer = styled(Box, {
  name: 'StatusPoll',
  slot: 'Answer'
})(({ theme }) => ({
  borderRadius: theme.shape.borderRadius / 2,
  border: theme.mixins.border('secondary'),
  fontSize: theme.mixins.pxToRem(15),
  height: theme.spacing(6),
  display: 'flex',
  alignItems: 'center',
  marginBottom: theme.spacing(2),
  color: theme.palette.text.hint,
  padding: theme.spacing(0, 2)
}));

export default function AttachPollPreview({
  value,
  formUrl,
  handleEdit: actionEdit,
  handleRemove: actionRemove
}: AttachPollPreviewProps) {
  const poll = value;
  const { i18n, dialogBackend } = useGlobal();
  const formikContext = useFormikContext();

  const handleRemove = React.useCallback(
    () => {
      dialogBackend
        .confirm({
          message: i18n.formatMessage({
            id: 'are_you_sure'
          })
        })
        .then(oke => {
          if (!oke) return;

          if (actionRemove) {
            actionRemove();
          }
        });
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    []
  );

  const handleEdit = () => {
    dialogBackend
      .present({
        component: 'poll.dialog.AttachPollDialog',
        props: { initialValues: poll, formUrl, isEdit: true }
      })
      .then(value => {
        if (!value) return;

        actionEdit(value);
      });
  };

  if (!poll) return null;

  const { permissions } = poll;
  const canEdit = permissions?.can_edit ?? true;
  const canRemove = permissions?.can_delete ?? true;

  return (
    <PollWrapper>
      <TruncateText showFull variant="h4" fontSize={18} sx={{ mb: 2 }}>
        {poll.poll_question}
      </TruncateText>
      <div>
        {poll.poll_answers?.map((item: { answer: string }, index: number) => {
          return item.answer ? (
            <Answer key={index.toString()}>{item.answer}</Answer>
          ) : null;
        })}
      </div>
      <ButtonList>
        {canEdit ? (
          <Button
            disabled={formikContext?.isSubmitting}
            variant="outlined"
            size="smaller"
            sx={{ fontWeight: 'bold' }}
            onClick={handleEdit}
          >
            {i18n.formatMessage({ id: 'edit' })}
          </Button>
        ) : null}
        {canRemove ? (
          <Button
            disabled={formikContext?.isSubmitting}
            variant="outlined"
            size="smaller"
            sx={{ fontWeight: 'bold' }}
            onClick={handleRemove}
          >
            {i18n.formatMessage({ id: 'remove' })}
          </Button>
        ) : null}
      </ButtonList>
      {poll.poll_close_time && poll.enable_close ? (
        <Box sx={{ mt: 2 }}>
          <Typography variant="body2" color="text.hint" fontWeight="bold">
            {i18n.formatMessage({ id: 'expire_on' })}{' '}
            {moment(poll.poll_close_time).format('LLL')}
          </Typography>
        </Box>
      ) : null}
    </PollWrapper>
  );
}

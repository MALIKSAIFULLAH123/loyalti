/**
 * @type: ui
 * name: StatusComposerControlAttachedPoll
 */

import { StatusComposerControlProps, useGlobal } from '@metafox/framework';
import { ButtonList, LineIcon, TruncateText } from '@metafox/ui';
import { Box, Button, Typography } from '@mui/material';
import { styled } from '@mui/material/styles';
import { get } from 'lodash';
import moment from 'moment';
import React from 'react';

const PollWrapper = styled(Box, {
  name: 'StatusPoll',
  slot: 'Root'
})(({ theme }) => ({
  borderRadius: theme.shape.borderRadius,
  border: theme.mixins.border('secondary'),
  padding: theme.spacing(3),
  margin: theme.spacing(2)
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

const StyledAttachmentItem = styled(Box, {
  name: 'AttachmentItem',
  slot: 'FormItem'
})(({ theme }) => ({
  display: 'flex',
  margin: theme.spacing(1, 0),
  fontSize: theme.mixins.pxToRem(13),
  lineHeight: 1.5,
  '& .info': {
    display: 'flex'
  }
}));
const AttachmentIcon = styled(LineIcon, { name: 'AttachmentIcon' })(
  ({ theme }) => ({
    paddingRight: theme.spacing(0.5),
    fontSize: theme.mixins.pxToRem(15)
  })
);

export default function StatusComposerControlAttachedPoll({
  composerRef
}: StatusComposerControlProps) {
  const poll = get(composerRef.current.state, 'attachments.poll.value');
  const { i18n, dialogBackend } = useGlobal();

  if (!poll) return null;

  const handleRemove = () => {
    composerRef.current.removeAttachmentName('poll');
  };

  const handleEdit = () => {
    dialogBackend
      .present({
        component: 'poll.dialog.AddPollToStatusComposerDialog',
        props: { initialValues: poll, isEdit: true }
      })
      .then(value => {
        if (!value) return;

        composerRef.current.setAttachments('poll', 'poll', {
          value,
          as: 'StatusComposerControlAttachedPoll'
        });
      });
  };

  return (
    <PollWrapper>
      <TruncateText showFull variant="h4" fontSize={18} sx={{ mb: 2 }} fontWeight={600}>
        {poll.poll_question}
      </TruncateText>
      <div>
        {poll.poll_answers?.map((item, index) => {
          return item.answer ? (
            <Answer key={index.toString()}>{item.answer}</Answer>
          ) : null;
        })}
      </div>
      {poll.poll_attachments &&
        poll.poll_attachments.map((item, index) => {
          return (
            <StyledAttachmentItem key={index}>
              <div className="info">
                <AttachmentIcon icon="ico-paperclip-alt" />
                <div>{item.file_name}</div>
              </div>
            </StyledAttachmentItem>
          );
        })}
      <ButtonList sx={{ pb: 3, pt: 1 }}>
        <Button
          variant="outlined"
          size="smaller"
          sx={{ fontWeight: 'bold' }}
          onClick={handleEdit}
        >
          {i18n.formatMessage({ id: 'edit' })}
        </Button>
        <Button
          variant="outlined"
          size="smaller"
          sx={{ fontWeight: 'bold' }}
          onClick={handleRemove}
        >
          {i18n.formatMessage({ id: 'remove' })}
        </Button>
      </ButtonList>
      {poll.poll_close_time && poll.enable_close ? (
        <Typography variant="body2" color="text.hint" fontWeight="bold">
          {i18n.formatMessage({ id: 'expire_on' })}{' '}
          {moment(poll.poll_close_time).format('LLL')}
        </Typography>
      ) : null}
    </PollWrapper>
  );
}

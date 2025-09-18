/**
 * @type: service
 * name: HistoryEditedCommentButton
 */
import { useGlobal } from '@metafox/framework';
import { ActButton, ActButtonProps } from '@metafox/ui';
import React from 'react';
import { Tooltip } from '@mui/material';

export type HistoryEditedCommentButtonProps = ActButtonProps & {
  handleAction: any;
  identity: string;
};

export default function HistoryEditedCommentButton({
  handleAction,
  identity,
  ...rest
}: HistoryEditedCommentButtonProps) {
  const { i18n } = useGlobal();

  const onClick = () => {
    handleAction('comment/showHistoryDialog');
  };

  return (
    <Tooltip title={i18n.formatMessage({ id: 'show_edit_history' })}>
      <span>
        <ActButton
          data-testid="historyButton"
          onClick={onClick}
          label={i18n.formatMessage({ id: 'edited' })}
          {...rest}
        />
      </span>
    </Tooltip>
  );
}

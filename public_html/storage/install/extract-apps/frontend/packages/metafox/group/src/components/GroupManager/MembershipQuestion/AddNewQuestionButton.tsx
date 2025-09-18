/**
 * @type: ui
 * name: group.addNewQuestionButton
 * chunkName: group
 */

import { useGetItem, useGlobal, useResourceAction } from '@metafox/framework';
import { compactData } from '@metafox/utils';
import { Button, styled } from '@mui/material';
import React from 'react';
import qs from 'query-string';
import { get } from 'lodash';

const StyledButton = styled(
  Button,
  {}
)(({ disabled, theme }) => ({
  ...(disabled && {
    color: `${theme.palette.text.disabled} !important`
  })
}));

const AddNewQuestionButton = ({ disabled }) => {
  const { i18n, usePageParams, dispatch, getSetting } = useGlobal();
  const { identity } = usePageParams();
  const pagingItem = useGetItem('pagination');
  const { apiUrl, apiParams } = useResourceAction(
    'group',
    'group_question',
    'viewAll'
  );
  const setting = getSetting();
  const max_membership_questions =
    get(setting, 'group.maximum_membership_question') || (0 as number);
  const group_id = identity.split('.')[3];

  const pagingId = `${apiUrl}?${qs.stringify(
    compactData(apiParams, { id: group_id })
  )}`;

  const isMaximumQuestion =
    max_membership_questions <= pagingItem[pagingId]?.ids?.length;

  const handleClick = () => {
    dispatch({
      type: 'group/addNewMembershipQuestionForm',
      payload: { identity }
    });
  };

  return (
    <StyledButton
      onClick={handleClick}
      variant="text"
      disabled={disabled || isMaximumQuestion}
    >
      {i18n.formatMessage({ id: 'add' })}
    </StyledButton>
  );
};

export default AddNewQuestionButton;

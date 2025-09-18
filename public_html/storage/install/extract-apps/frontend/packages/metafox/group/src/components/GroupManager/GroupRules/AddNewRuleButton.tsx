/**
 * @type: ui
 * name: group.addNewRuleButton
 * chunkName: group
 */

import { useGetItem, useGlobal, useResourceAction } from '@metafox/framework';
import { compactData } from '@metafox/utils';
import { Button } from '@mui/material';
import React from 'react';
import qs from 'query-string';
import { get } from 'lodash';

const AddNewQuestionButton = () => {
  const { i18n, dispatch, usePageParams, getSetting } = useGlobal();
  const { identity } = usePageParams();
  const pagingItem = useGetItem('pagination');

  const { apiUrl, apiParams } = useResourceAction(
    'group',
    'group_rule',
    'viewAll'
  );

  const setting = getSetting();
  const max_rules =
    get(setting, 'group.maximum_number_group_rule') || (0 as number);
  const group_id = identity.split('.')[3];

  const pagingId = `${apiUrl}?${qs.stringify(
    compactData(apiParams, { id: group_id })
  )}`;

  const isMaximumRule = max_rules <= pagingItem[pagingId].ids.length;

  const handleClick = () => {
    dispatch({ type: 'addGroupRule', payload: { identity } });
  };

  return (
    <Button onClick={handleClick} variant="text" disabled={isMaximumRule}>
      {i18n.formatMessage({ id: 'add' })}
    </Button>
  );
};

export default AddNewQuestionButton;

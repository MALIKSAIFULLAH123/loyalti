/* eslint-disable @typescript-eslint/no-unused-vars */
import {
  BlockViewProps,
  useGlobal,
  useResourceAction
} from '@metafox/framework';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import { APP_GROUP } from '@metafox/group';
import React from 'react';

export type Props = BlockViewProps;

const MembershipQuestion = ({ title, ...rest }: Props) => {
  const { i18n, ListView } = useGlobal();
  const { apiUrl, apiParams = 'group_id=:id' } = useResourceAction(
    APP_GROUP,
    'group_question',
    'viewAll'
  );
  const dataSource = { apiUrl, apiParams };

  return null;
};

export default MembershipQuestion;

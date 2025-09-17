import { BlockViewProps } from '@metafox/framework';
import { RemoteFormBuilder } from '@metafox/form';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import React, { useMemo } from 'react';

export type Props = BlockViewProps;

export default function UserRegisterBlock({ blockProps, title }: Props) {
  const dataSource = useMemo(() => {
    return { apiUrl: '/user/form' };
  }, []);

  const initialValues = useMemo(() => {
    return {};
  }, []);

  return (
    <Block>
      <BlockHeader title={title} />
      <BlockContent>
        <RemoteFormBuilder
          submitAction="user/register"
          dataSource={dataSource}
          initialValues={initialValues}
        />
      </BlockContent>
    </Block>
  );
}

import { BlockViewProps, useGlobal } from '@metafox/framework';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import { EditInfoItem } from '@metafox/ui';
import { compactUrl } from '@metafox/utils';
import * as React from 'react';

export type Props = BlockViewProps & {
  identity: string;
  data?: {
    apiMethod?: 'get' | 'post' | 'put' | 'patch' | 'delete';
    apiUrl?: string;
  };
  appName?: string;
  resourceName?: string;
};

export default function BlockEditItem({
  data: config,
  identity,
  title,
  appName = '',
  resourceName = ''
}: Props) {
  const { i18n, useFetchItems, jsxBackend } = useGlobal();
  const id = identity.split('.')[3];
  const [listData, setListData] = React.useState([]);

  const [data, loading] = useFetchItems({
    dataSource: {
      apiUrl: compactUrl(config.apiUrl, { id })
    }
  });

  React.useEffect(() => {
    if (!loading) setListData(data);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [loading]);

  if (!identity) return null;

  if (loading)
    return (
      <Block>{jsxBackend.render({ component: 'form.DefaultLoading' })}</Block>
    );

  return (
    <Block>
      <BlockHeader title={i18n.formatMessage({ id: title })} />
      <BlockContent>
        {listData.map((item, index) => (
          <EditInfoItem
            data={item}
            key={index}
            index={index}
            loaded={!loading}
            setListData={setListData}
            appName={appName}
            resourceName={resourceName}
          />
        ))}
      </BlockContent>
    </Block>
  );
}

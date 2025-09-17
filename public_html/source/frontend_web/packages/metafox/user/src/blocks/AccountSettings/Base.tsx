import {
  BlockViewProps,
  useGlobal,
  useResourceAction
} from '@metafox/framework';
// layouts
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
// actions
import { AppState } from '@metafox/user/types';
import React from 'react';
// types
import { EditInfoItem } from '@metafox/ui';
import { useFetchItems } from '@metafox/rest-client';
import { APP_USER } from '@metafox/user/constant';

export type Props = BlockViewProps & AppState['accountSettings'];

const GeneralSettings = ({ title = 'account_settings' }: Props) => {
  const { i18n, jsxBackend } = useGlobal();

  const dataSource = useResourceAction(
    APP_USER,
    APP_USER,
    'getAccountSettings'
  );

  const [listData, setListData] = React.useState([]);

  const [data, loading] = useFetchItems({
    dataSource
  });

  React.useEffect(() => {
    if (!loading) setListData(data);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [loading]);

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
            appName={APP_USER}
            resourceName={APP_USER}
          />
        ))}
      </BlockContent>
    </Block>
  );
};

export default GeneralSettings;

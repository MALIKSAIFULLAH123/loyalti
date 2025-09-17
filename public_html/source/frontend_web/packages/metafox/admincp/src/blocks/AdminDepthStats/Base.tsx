import { useGlobal, Link } from '@metafox/framework';
import { Block, BlockContent, BlockHeader, BlockTitle } from '@metafox/layout';
import { Box, Skeleton, styled } from '@mui/material';
import React from 'react';
import DialogDetail from './DialogDetail';
import ItemStat from './ItemStat';
import { isEmpty } from 'lodash';

const Wrapper = styled(Box, { slot: 'Box' })(({ theme }) => ({
  overflow: 'auto'
}));

const ViewMore = styled(Link, { slot: 'ViewMore' })(({ theme }) => ({
  color: '#2681d5',
  paddingTop: theme.spacing(2),
  paddingLeft: theme.spacing(1),
  display: 'block',
  cursor: 'pointer'
}));

const TOTAL_ROW = 5;

export interface Props extends UIBlockViewProps {}

export default function AdminItemStats({ title }: Props) {
  const { useFetchDetail, i18n } = useGlobal();

  const [data, loading] = useFetchDetail({
    dataSource: {
      apiUrl: 'admincp/dashboard/deep-statistic'
    }
  });

  const [open, setOpen] = React.useState(false);

  const [tabId, setTabId] = React.useState<number>(0);

  const changeTab = (_, value) => {
    setTabId(value);
  };

  const handleClickOpen = () => {
    setOpen(true);
  };

  const handleClose = (value: string) => {
    setOpen(false);
  };

  if (loading) {
    return (
      <Block>
        <BlockHeader title={i18n.formatMessage({ id: title })} />
        <BlockContent>
          <Skeleton variant="text" width={200} />
          <Skeleton variant="text" width={200} />
          <Skeleton variant="text" width={250} />
        </BlockContent>
      </Block>
    );
  }

  if (isEmpty(data)) return null;

  const dataStatistic = Object.values(data);

  const totalStatisticApp = Math.max(
    TOTAL_ROW - data?.site_statistic?.items?.length,
    1
  );

  return (
    <Block>
      <Wrapper>
        <BlockHeader>
          <BlockTitle>{i18n.formatMessage({ id: title })}</BlockTitle>
        </BlockHeader>
        <BlockContent>
          {dataStatistic.map((x: Record<string, any>, index) => (
            <ItemStat
              key={`k${index}`}
              item={x}
              tabId={tabId}
              changeTab={changeTab}
              limit={totalStatisticApp}
            />
          ))}
          <ViewMore onClick={handleClickOpen}>
            {i18n.formatMessage({ id: 'view_more' })}
          </ViewMore>
          {open ? (
            <DialogDetail
              onClose={handleClose}
              data={dataStatistic}
              title={title}
              tabId={tabId}
            />
          ) : null}
        </BlockContent>
      </Wrapper>
    </Block>
  );
}

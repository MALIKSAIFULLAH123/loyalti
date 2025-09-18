import { APP_NAME, TYPE_CLICK, TYPE_VIEW } from '@metafox/advertise/constants';
import { useGlobal, useResourceAction } from '@metafox/framework';
import { Box, Grid } from '@mui/material';
import React from 'react';

interface Props {
  isViewChart: boolean;
  isSearch: boolean;
  pageParamsInitSearch?: any;
}

const StatisticView = ({
  isViewChart,
  pageParamsInitSearch,
  isSearch
}: Props) => {
  const { jsxBackend, useIsMobile, useFetchDetail, usePageParams } =
    useGlobal();

  const isMobile = useIsMobile();
  const pageParams = usePageParams();

  const dataSourceClick = useResourceAction(
    APP_NAME,
    APP_NAME,
    'viewClickReport'
  );

  const dataSourceImpression = useResourceAction(
    APP_NAME,
    APP_NAME,
    'viewImpressionReport'
  );

  const params = React.useMemo(() => {
    if (!isSearch && pageParamsInitSearch) {
      return pageParamsInitSearch;
    }

    return pageParams;
  }, [isSearch, pageParamsInitSearch, pageParams]);

  const [dataClick, loadingClick] = useFetchDetail({
    dataSource: params?.view ? dataSourceClick : null,
    pageParams: params
  });

  const [dataImpression, loadingImpression] = useFetchDetail({
    dataSource: params?.view ? dataSourceImpression : null,
    pageParams: params
  });

  const data = React.useMemo(
    () => [
      {
        grid: 6,
        grid_mobile: 12,
        icon: 'ico-mouse-alt',
        type: TYPE_CLICK,
        total: dataClick?.total,
        phrase: dataClick?.phrase,
        view: dataClick?.view,
        data: dataClick?.statistic
      },
      {
        grid: 6,
        grid_mobile: 12,
        icon: 'ico-eye',
        type: TYPE_VIEW,
        total: dataImpression?.total,
        phrase: dataImpression?.phrase,
        view: dataImpression?.view,
        data: dataImpression?.statistic
      }
    ],
    [dataClick, dataImpression]
  );

  const ItemTable = jsxBackend.get('advertise.itemView.itemTable');

  const ItemChart = jsxBackend.get('advertise.itemView.itemChart');

  if (isViewChart) {
    return (
      <Box sx={{ my: 2 }}>
        <Grid container spacing={2}>
          {data.map(item => (
            <Grid
              key={item.type}
              item
              xs={isMobile ? item.grid_mobile : item.grid}
            >
              <ItemChart
                data={item}
                loading={
                  item.type === TYPE_CLICK ? loadingClick : loadingImpression
                }
              />
            </Grid>
          ))}
        </Grid>
      </Box>
    );
  }

  return (
    <Box sx={{ my: 2 }}>
      <Grid container spacing={2}>
        {data.map(item => (
          <Grid
            key={item.type}
            item
            xs={isMobile ? item.grid_mobile : item.grid}
          >
            <ItemTable
              data={item}
              loading={
                item.type === TYPE_CLICK ? loadingClick : loadingImpression
              }
            />
          </Grid>
        ))}
      </Grid>
    </Box>
  );
};

export default React.memo(StatisticView);

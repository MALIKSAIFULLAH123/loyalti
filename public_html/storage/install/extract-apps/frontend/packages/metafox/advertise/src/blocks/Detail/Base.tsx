import {
  BlockViewProps,
  Link,
  useGlobal,
  useResourceAction
} from '@metafox/framework';
import { styled, Box, IconButton, Button, Skeleton } from '@mui/material';
import React from 'react';
import { RemoteFormBuilder } from '@metafox/form';
import { compactUrl, whenParamRules } from '@metafox/utils';
import qs from 'querystring';
import { APP_NAME } from '../../constants';
import { LineIcon } from '@metafox/ui';
import StatisticView from './StatisticView';
import { isEmpty } from 'lodash';

export type Props = BlockViewProps & {
  detailActionMenu?: any;
  item: any;
  identity: string;
};

const name = 'Detail-Advertise';

const RootStyled = styled(Box, { name, slot: 'root' })(({ theme }) => ({
  backgroundColor: theme.palette.background.paper,
  padding: theme.spacing(2),
  borderBottomLeftRadius: theme.shape.borderRadius,
  borderBottomRightRadius: theme.shape.borderRadius
}));

const ButtonStyled = styled(IconButton, { name })(() => ({
  transform: 'translate(-7px,0)'
}));

const ChartWrapperStyled = styled(Box, { name })(({ theme }) => ({
  margin: theme.spacing(2, 0)
}));

const RowSearchStyled = styled(Box, { name })(({ theme }) => ({
  display: 'flex',
  flexDirection: 'row',
  justifyContent: 'space-between',
  alignItems: 'center',
  [theme.breakpoints.down('sm')]: {
    flexDirection: 'column'
  }
}));

const LineIconStyled = styled(LineIcon, {
  name,
  shouldForwardProp: props => props !== 'active'
})<{ active?: boolean }>(({ theme, active }) => ({
  ...(active && {
    color: theme.palette.primary.main
  })
}));

const ActionButtonStyled = styled(Box, {
  name,
  shouldForwardProp: props => props !== 'isFormSearch'
})<{ isFormSearch?: boolean }>(({ theme, isFormSearch }) => ({
  marginLeft: 'auto',
  [theme.breakpoints.down('sm')]: {
    marginLeft: 'inherit',
    marginRight: 'auto',
    marginTop: theme.spacing(1)
  }
}));

export default function Base({ detailActionMenu, identity, item }: Props) {
  const { navigate, jsxBackend, i18n, usePageParams } = useGlobal();

  const [viewChart, setViewChart] = React.useState(true);

  const DetailInfo = jsxBackend.get('advertise.block.itemDetailInfo');
  const dataSource = useResourceAction(APP_NAME, APP_NAME, 'viewItem');
  const { id } = usePageParams();

  const [pageParamsInitSearch, setPageParamsInitSearch] = React.useState(null);

  const [isSearch, setIsSearch] = React.useState(false);
  const [errorSearch, setErrorSearch] = React.useState(null);
  const dataSourceForm = useResourceAction(APP_NAME, APP_NAME, 'getChartForm');

  const submitFilter = (values, form) => {
    const apiRules = dataSource.apiRules;

    const view = values.view;
    const start_date = values[`start_date_${view}`];
    const end_date = values[`end_date_${view}`];

    const params = whenParamRules(
      {
        view,
        [`start_date_${view}`]: start_date,
        [`end_date_${view}`]: end_date
      },
      apiRules
    );

    navigate(`?${qs.stringify(params)}`, { replace: true });

    setPageParamsInitSearch(null);
    setIsSearch(true);

    form.setSubmitting(false);
  };

  const toggleTypeView = () => {
    setViewChart(pre => !pre);
  };

  const handleExport = () => {};

  const onLoaded = ({ data }: any) => {
    if (!data || !data.value) return;

    const view = data.value.view;
    const start_date = data.value[`start_date_${view}`];
    const end_date = data.value[`end_date_${view}`];

    setPageParamsInitSearch({
      id,
      view,
      [`start_date_${view}`]: start_date,
      [`end_date_${view}`]: end_date
    });
  };

  const onFailure = error => {
    setErrorSearch(error);
  };

  if (!item) return null;

  const LoadingView = (
    <Box
      sx={{ display: 'flex', alignItems: 'center', justifyContent: 'center' }}
    >
      <Skeleton animation="wave" height={30} width={200} />
      <Skeleton animation="wave" height={30} width={200} />
      <Skeleton animation="wave" height={30} width={200} />
    </Box>
  );

  return (
    <RootStyled>
      <Box sx={{ mb: 2, mt: 1 }}>
        <Link to="/advertise" underline="hover" color="primary">
          {i18n.formatMessage({ id: 'back_to_all_ads' })}
        </Link>
      </Box>
      <DetailInfo
        identity={identity}
        item={item}
        detailActionMenu={detailActionMenu}
      />
      <ChartWrapperStyled>
        {dataSourceForm && isEmpty(errorSearch) ? (
          <Box>
            <RowSearchStyled>
              <RemoteFormBuilder
                hideWhenError
                loadingComponent={LoadingView}
                navigationConfirmWhenDirty={false}
                dataSource={{
                  apiMethod: dataSourceForm.apiMethod,
                  apiUrl: compactUrl(dataSourceForm.apiUrl, { id })
                }}
                onLoaded={onLoaded}
                onFailure={onFailure}
                onSubmit={submitFilter}
              />

              <ActionButtonStyled>
                <ButtonStyled
                  size="small"
                  onClick={toggleTypeView}
                  role="button"
                  id="back"
                  data-testid="buttonChart"
                >
                  <LineIconStyled active={viewChart} icon="ico-stats-dots" />
                </ButtonStyled>
                <ButtonStyled
                  size="small"
                  onClick={toggleTypeView}
                  role="button"
                  id="back"
                  data-testid="buttonTable"
                >
                  <LineIconStyled active={!viewChart} icon="ico-th-o" />
                </ButtonStyled>
              </ActionButtonStyled>
            </RowSearchStyled>
            <StatisticView
              isViewChart={viewChart}
              pageParamsInitSearch={pageParamsInitSearch}
              isSearch={isSearch}
            />
          </Box>
        ) : null}
        {item?.extra?.can_export ? (
          <Button
            onClick={handleExport}
            data-testid="buttonExport"
            color="primary"
            variant="outlined"
          >
            {i18n.formatMessage({ id: 'export' })}
          </Button>
        ) : null}
      </ChartWrapperStyled>
    </RootStyled>
  );
}

Base.displayName = 'Advertise';

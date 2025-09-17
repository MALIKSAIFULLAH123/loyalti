import { Block, BlockContent } from '@metafox/layout';
import { UIBlockViewProps } from '@metafox/ui';
import React, { useEffect } from 'react';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend
} from 'chart.js';
import { Line } from 'react-chartjs-2';
import {
  Box,
  FormControl,
  InputLabel,
  MenuItem,
  Select,
  SelectChangeEvent,
  styled
} from '@mui/material';
import { useGlobal, useSession } from '@metafox/framework';
import { groupBy, last, map, max, sumBy, get, min } from 'lodash';
import moment from 'moment';

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend
);

const calculateData = (
  type?: string,
  data?: Record<string, any>[],
  key: string = 'data'
) => {
  if (!data) return 0;

  if (type === 'sum') {
    return sumBy(data, key);
  }

  if (type === 'min') {
    return min(data.map(x => get(x, key)));
  }

  if (type === 'max') {
    return max(data.map(x => get(x, key)));
  }

  return sumBy(data, key);
};

const BlockContentWrapper = styled(BlockContent, { slot: 'BlockContent' })(
  ({ theme }) => ({
    height: '100%',
    display: 'flex',
    flexDirection: 'column',
    justifyContent: 'space-between'
  })
);

const HeaderWrapper = styled('div', { slot: 'HeaderWrapper' })(({ theme }) => ({
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',
  marginBottom: theme.spacing(3),
  [theme.breakpoints.down('lg')]: {
    flexDirection: 'column'
  }
}));

const HeaderLeftWrapper = styled('div', { slot: 'HeaderLeftWrapper' })(
  ({ theme }) => ({
    height: 'auto',
    [theme.breakpoints.down('lg')]: {
      marginBottom: theme.spacing(2)
    }
  })
);

const HeaderRightWrapper = styled('div', { slot: 'HeaderRightWrapper' })(
  ({ theme }) => ({
    height: 'auto',
    '& .MuiSelect-select': {
      paddingTop: theme.spacing(1),
      paddingBottom: theme.spacing(1)
    }
  })
);

const TitleHeader = styled('h3', { slot: 'TitleHeader' })(({ theme }) => ({
  fontSize: theme.spacing(2.5),
  margin: theme.spacing(0),
  fontWeight: 'normal'
}));

const Time = styled('span', { slot: 'Time' })(({ theme }) => ({
  color: theme.palette.grey[700],
  height: theme.spacing(2),
  display: 'block'
}));

const Chart = styled('div', { slot: 'Chart' })(({ theme }) => ({
  color: theme.palette.grey[700],
  position: 'relative',
  flex: 1,
  minHeight: 0,
  '&.loading canvas': {
    opacity: 0.1
  }
}));

const Loading = styled(Box, { slot: 'Loading' })(({ theme }) => ({
  height: '100%',
  width: '100%',
  justifyContent: 'center',
  display: 'flex',
  position: 'absolute'
}));

export interface Props extends UIBlockViewProps {}

const cacheData = {};

type PeriodTimeType = 'day' | 'week' | 'month';
const convertPeriod = periodType => {
  let period: PeriodTimeType = null;

  if (periodType === '1d') {
    period = 'day';
  }

  if (periodType === '1w') {
    period = 'week';
  }

  if (periodType === '1M') {
    period = 'month';
  }

  return period;
};
type ChartDataType = {
  data: Record<string, any>[];
  from?: string;
  to?: string;
};
export default function AdminItemStats(props: Props) {
  const { useFetchDetail, apiClient, jsxBackend, i18n, useTheme } = useGlobal();
  const [chartData, setChartData] = React.useState<ChartDataType>({ data: [] });
  const [selectedType, setSelectedType] = React.useState('user');
  const [periodType, setPeriodType] = React.useState('1d');
  const [loading, setLoading] = React.useState(true);
  const theme = useTheme();
  const controllerRef = React.useRef<AbortController | null>(null);

  const session = useSession();

  const [typeList] = useFetchDetail<object>({
    dataSource: {
      apiUrl: 'admincp/dashboard/stat-type'
    }
  });

  const typeData = typeList?.types?.find(x => x.value === selectedType) || {};

  const getData = React.useCallback(
    async (name: string, period: string) => {
      if (controllerRef.current) {
        controllerRef.current.abort();
      }

      if (cacheData[`${name}_${period}`]) {
        setChartData(cacheData[`${name}_${period}`]);
        setLoading(false);

        return;
      }

      controllerRef.current = new AbortController();
      const signal = controllerRef.current.signal;

      setLoading(true);
      try {
        const response = await apiClient.request({
          method: 'get',
          url: `admincp/dashboard/chart?name=${name}&period=${period}`,
          signal
        });

        if (response) {
          const data = {
            data: response?.data?.data,
            from: response?.data?.meta?.from,
            to: response?.data?.meta?.to
          };
          cacheData[`${name}_${period}`] = data;
          setChartData(data);
          setLoading(false);
        }
      } catch (error) {}
    },
    [apiClient]
  );

  const handleTypeChage = (event: SelectChangeEvent) => {
    setSelectedType(event.target.value);
    getData(event.target.value, periodType);
  };

  const handlePeriodChage = (event: SelectChangeEvent) => {
    setPeriodType(event.target.value);
    getData(selectedType, event.target.value);
  };

  useEffect(() => {
    getData(selectedType, periodType);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const formatGroupTime = React.useCallback(
    (date: any, type?: '1d' | '1w' | '1M') => {
      const period = type ?? periodType;

      if (period === '1d') {
        return moment(date).format('DD MMM');
      }

      if (period === '1w') {
        const dateTimeLine =
          moment().week() === moment(date).week()
            ? moment()
            : moment(date).endOf('week');

        return dateTimeLine.format('DD MMM');
      }

      if (period === '1M') {
        return moment(date).format('MMM YYYY');
      }
    },
    [periodType]
  );

  const fillMissingDates = React.useCallback(
    (original, from: any, to: any) => {
      if (!from || !to)
        return original.sort(
          (a, b) => moment(a.time).unix() - moment(b.time).unix()
        );

      const allDates = [];
      const currentDate = moment(from);
      const period = convertPeriod(periodType);

      while (currentDate.isSameOrBefore(to, period)) {
        const label = formatGroupTime(currentDate);
        const itemExist = original.find(x => x.label === label);

        allDates.push(itemExist ? itemExist : { label, data: 0 });
        currentDate.add(1, period);
      }

      return allDates;
    },
    [periodType, formatGroupTime]
  );

  const dataGroupTime = groupBy(chartData?.data, ({ date }) =>
    formatGroupTime(date)
  );
  const dataGroup = map(dataGroupTime, (items, label) => {
    const data = calculateData(typeData.operation, items, 'data');

    return { label, data, time: last(items)?.date };
  });

  const fullPeriodData = fillMissingDates(
    dataGroup,
    chartData?.from,
    chartData?.to
  );
  const { labels, dataValues } = fullPeriodData.reduce(
    (acc, item) => {
      acc.labels.push(item.label);
      acc.dataValues.push(item.data);

      return acc;
    },
    { labels: [], dataValues: [] }
  );
  const data = {
    labels,
    datasets: [
      {
        data: dataValues,
        borderColor: theme.palette.primary.main,
        backgroundColor: theme.palette.primary.main
      }
    ]
  };

  const options = {
    responsive: true,
    maintainAspectRatio: false,
    clip: false,
    plugins: {
      legend: false,
      title: {
        display: false
      }
    },
    scales: {
      y: {
        beginAtZero: true,
        min: 0,
        ticks: { stepSize: parseInt(max(dataValues), 10) > 50 ? 10 : 1 }
      }
    }
  };

  return (
    <Block>
      <BlockContentWrapper>
        <HeaderWrapper>
          <HeaderLeftWrapper>
            <TitleHeader>
              {i18n.formatMessage(
                { id: 'welcome_back_username' },
                { username: session.user.full_name }
              )}
            </TitleHeader>
            <Time>
              {!loading ? (
                <>
                  {labels[0]} - {labels[labels.length - 1]}{' '}
                </>
              ) : null}
            </Time>
          </HeaderLeftWrapper>
          <HeaderRightWrapper>
            <FormControl sx={{ m: 1, minWidth: 200 }} size="small">
              <InputLabel>{i18n.formatMessage({ id: 'type' })}</InputLabel>
              <Select
                value={selectedType}
                label="Type"
                onChange={handleTypeChage}
              >
                {typeList?.types?.map((item, index) => (
                  <MenuItem key={index} value={item.value}>
                    {item.label}
                  </MenuItem>
                ))}
              </Select>
            </FormControl>
            <FormControl sx={{ m: 1, minWidth: 200 }} size="small">
              <InputLabel>{i18n.formatMessage({ id: 'view' })}</InputLabel>
              <Select
                value={periodType}
                label="View"
                onChange={handlePeriodChage}
              >
                {typeList?.period?.map((item, index) => (
                  <MenuItem key={index} value={item.value}>
                    {item.label}
                  </MenuItem>
                ))}
              </Select>
            </FormControl>
          </HeaderRightWrapper>
        </HeaderWrapper>
        <Chart className={loading ? 'loading' : ''}>
          {loading ? (
            <Loading>
              {jsxBackend.render({ component: 'form.DefaultLoading' })}
            </Loading>
          ) : (
            <Line width={500} data={data} options={options} />
          )}
        </Chart>
      </BlockContentWrapper>
    </Block>
  );
}

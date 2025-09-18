/**
 * @type: itemView
 * name: advertise.itemView.itemChart
 * chunkName: advertise
 */

import React from 'react';
import { styled, Box, Typography } from '@mui/material';
import { Line } from 'react-chartjs-2';
import moment from 'moment';
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
import { TYPE_CLICK } from '@metafox/advertise/constants';
import { FormatNumber, LineIcon } from '@metafox/ui';
import SkeletonLoading from './LoadingSkeleton';
import { useGlobal } from '@metafox/framework';

const name = 'Table';

const Root = styled(Box, { name, slot: 'root' })(({ theme }) => ({
  minHeight: '200px',
  width: '100%',
  display: 'flex',
  flexDirection: 'column'
}));

const Chart = styled('div', { name, slot: 'chart' })(({ theme }) => ({
  color: theme.palette.grey[700],
  height: '145px',
  width: '100%'
}));

const LabelStyled = styled(Box, { name, slot: 'label' })(({ theme }) => ({
  display: 'flex',
  margin: theme.spacing(1)
}));

const LineIconStyled = styled(LineIcon, {
  name,
  slot: 'icon',
  shouldForwardProp: props => props !== 'size'
})<{ size?: any }>(({ theme, size }) => ({
  color: theme.palette.text.secondary,
  marginRight: theme.spacing(2),
  ...(size && {
    fontSize: theme.mixins.pxToRem(size)
  })
}));

const TextStyled = styled(Typography, { name, slot: 'label' })(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(18),
  marginLeft: theme.spacing(0.5)
}));

interface Props {
  data: any;
  loading: boolean;
  error: any;
}

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend
);

function ItemView({ data, loading, error }: Props) {
  const { useTheme, i18n } = useGlobal();

  const theme = useTheme();

  const labels = [];
  const values = [];

  const options = React.useMemo(() => {
    return {
      maintainAspectRatio: false,
      responsive: true,
      plugins: {
        legend: false,
        title: {
          display: false
        }
      },
      scales: {
        yAxis: {
          grid: {
            drawBorder: true,
            color: theme.palette.mode === 'light' ? '#ededed' : '#616161' // #ededed light
          },
          ticks: {
            beginAtZero: false,
            color: theme.palette.mode === 'light' ? '#616161' : 'white',
            fontSize: 40
          }
        },
        xAxis: {
          grid: {
            drawBorder: true,
            color: theme.palette.mode === 'light' ? '#ededed' : '#616161' // #ededed light
          },
          ticks: {
            beginAtZero: false,
            color: theme.palette.mode === 'light' ? '#616161' : 'white', // '#616161' light
            fontSize: 40
          }
        }
      }
    };
  }, [theme.palette.mode]);

  const dataInput = React.useMemo(() => {
    return {
      labels,
      datasets: [
        {
          yAxisID: 'yAxis',
          xAxisID: 'xAxis',
          data: values,
          borderColor: data.type === TYPE_CLICK ? '#f02848' : '#2682d5',
          backgroundColor: data.type === TYPE_CLICK ? '#f02848' : '#2682d5',
          tension: 0.4,
          borderWidth: 4,
          pointRadius: 5
        }
      ]
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [values]);

  if (loading) {
    return (
      <Root>
        <SkeletonLoading />
      </Root>
    );
  }

  if (!data?.data || error) return null;

  if (data.data.length) {
    data.data.forEach(item => {
      values.push(item.value);

      if (data.view === 'day' && !item?.label) {
        const isValidDate = moment(item?.format?.iso).isValid();

        if (isValidDate) {
          labels.push(moment(item?.format?.iso).format('ll'));
        }

        return;
      }

      if (data.view === 'month' && !item?.label) {
        const isValidDate = moment(item?.format?.iso).isValid();

        if (isValidDate) {
          labels.push(moment(item?.format?.iso).format('MM[/]YYYY'));
        }

        return;
      }

      labels.push(item?.label);
    });
  }

  return (
    <Root>
      <Chart>
        <Line width="100%" height="100%" options={options} data={dataInput} />
      </Chart>
      <LabelStyled>
        <LineIconStyled icon={data.icon} size={data?.size || 24} />
        <Typography color="text.secondary" variant="subtitle1">
          {data?.total ? <FormatNumber value={data.total} /> : 0}
        </Typography>
        <TextStyled color="text.secondary">
          {data?.phrase
            ? i18n.formatMessage(
                { id: data.phrase.name },
                {
                  type: data.phrase.params.type,
                  total: data.phrase.params.total,
                  value: data.phrase.params.value
                }
              )
            : i18n.formatMessage({ id: 'click' })}
        </TextStyled>
      </LabelStyled>
    </Root>
  );
}

export default React.memo(ItemView);

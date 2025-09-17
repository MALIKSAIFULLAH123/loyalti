import { useGlobal } from '@metafox/framework';
import { Typography, TypographyProps } from '@mui/material';
import { styled } from '@mui/material/styles';
import React from 'react';

const StatItem = styled('span', {
  name: 'Statistic',
  slot: 'Item',
  shouldForwardProp: prop => prop !== 'dotSeparate'
})<{ dotSeparate?: boolean }>(({ dotSeparate, theme }) =>
  Object.assign(
    {
      display: 'inline-block'
    },
    dotSeparate && {
      '& + &:before': {
        color: theme.palette.text.secondary,
        content: '"·"',
        paddingLeft: '0.25em',
        paddingRight: '0.25em'
      }
    }
  )
);

export interface StatisticProps extends TypographyProps {
  component?: React.ElementType;
  values: Record<string, number>;
  display?: string;
  skipZero?: boolean;
  limit?: number;
  dotSeparate?: boolean;
  alignRight?: boolean;
  alginVertical?: boolean;
  fontStyle?: 'default' | 'minor';
  truthyValue?: boolean;
}

const Statistic = ({
  values = {},
  component = 'div',
  limit = 2,
  skipZero = true,
  color = 'text.secondary',
  display = 'total_like, total_view',
  dotSeparate = true,
  variant = 'body2',
  truthyValue = false,
  ...props
}: StatisticProps) => {
  const { i18n } = useGlobal();

  const items = React.useMemo<string[]>(() => {
    const fields = display
      .split(',')
      .map(x => x.trim())
      .filter(x => !!x);

    return skipZero ? fields.filter(x => 0 < values[x]) : fields;
  }, [values, display, skipZero]);

  if (!items.length) return null;

  return (
    <Typography
      variant={variant}
      component={component}
      data-testid="itemStatistic"
      color={color}
      {...props}
    >
      {items.map(id => (
        <StatItem dotSeparate={dotSeparate} key={id.toString()}>
          {!truthyValue || values[id]
            ? i18n.formatMessage(
                { id },
                {
                  value: values[id] || 0,
                  span: msg => <span children={msg} />
                }
              )
            : null}
        </StatItem>
      ))}
    </Typography>
  );
};

export default Statistic;

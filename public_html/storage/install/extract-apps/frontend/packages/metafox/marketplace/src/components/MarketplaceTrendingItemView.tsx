import { Link } from '@metafox/framework';
import { Skeleton, styled } from '@mui/material';
import * as React from 'react';

type TrendingItemProps = {
  to?: string;
  label?: string;
  backgroundColor?: string;
};

const name = 'TrendingItemView';

const Wrapper = styled('div', { name, slot: 'root' })(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  padding: theme.spacing(2),
  color: theme.palette.background.paper,
  borderRadius: theme.shape.borderRadius,
  fontSize: theme.mixins.pxToRem(18),
  fontWeight: theme.typography.fontWeightBold,
  minHeight: '100px'
}));

const randomColor = Math.floor(Math.random() * 16777215).toString(16);

const TrendingItemView = ({
  to,
  label,
  backgroundColor = randomColor
}: TrendingItemProps) => {
  return (
    <Wrapper style={{ backgroundColor: `#${backgroundColor}` }}>
      <Link to={to} children={label} color={'inherit'} />
    </Wrapper>
  );
};

const LoadingSkeleton = () => {
  return (
    <Wrapper>
      <Skeleton variant="rectangular" />;
    </Wrapper>
  );
};

TrendingItemView.LoadingSkeleton = LoadingSkeleton;

export default LoadingSkeleton;

import {
  ListViewBlockProps,
  useGlobal,
  useResourceAction,
  GlobalState
} from '@metafox/framework';
import { Block, BlockContent, BlockHeader, BlockTitle } from '@metafox/layout';
import React from 'react';
import {
  RESOURCE_SUBSCRIPTION_COMPARISON,
  APP_SUBSCRIPTION
} from '@metafox/subscription';
import {
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableRow,
  styled,
  Box,
  Typography,
  Skeleton
} from '@mui/material';
import { LineIcon, TruncateText } from '@metafox/ui';
import { useSelector } from 'react-redux';
import { isEmpty } from 'lodash';
import { SubscriptionPackageItemShape } from '@metafox/subscription/types';

export type Props = ListViewBlockProps;

const name = 'ComparisonBlock';

const TableCustom = styled(TableBody, { name, slot: 'tableCustom' })(
  ({ theme }) => ({
    overflow: 'hidden',
    display: 'inline-block',
    [theme.breakpoints.down('sm')]: {
      minWidth: 'auto'
    },
    '& .MuiTableCell-root': {
      fontSize: theme.mixins.pxToRem(13),
      color: theme.palette.text.secondary,
      minWidth: '195px',
      maxWidth: '195px',
      minHeight: '56px',
      cursor: 'pointer',
      position: 'relative',
      padding: theme.spacing(1),
      verticalAlign: 'middle',
      '&:first-of-type': {
        paddingLeft: 0,
        position: 'absolute',
        marginLeft: '-375px',
        minWidth: '375px',
        maxWidth: '375px',
        display: 'flex',
        alignItems: 'center',
        [theme.breakpoints.down('sm')]: {
          minWidth: 'inherit',
          maxWidth: '40vw',
          width: '100%',
          marginLeft: '-40vw'
        }
      },
      '&:hover:not(:first-of-type):after': {
        background: theme.palette.action.hover,
        content: '""',
        height: '10000px',
        left: '0',
        position: 'absolute',
        top: '-5000px',
        width: '100%',
        zIndex: '-1'
      }
    },
    '& .MuiTableRow-root': {
      verticalAlign: 'top',
      '& td': {
        borderTop: 'solid 1px',
        borderTopColor: theme.palette.border?.secondary
      }
    },
    '& .MuiTableRow-root:first-of-type': {
      '& td': {
        borderTopColor: 'transparent'
      }
    },
    '& .ico-check': {
      color: theme.palette.primary.main,
      fontSize: theme.mixins.pxToRem(15)
    },
    '& .ico-close': {
      color: theme.palette.text.disabled,
      fontSize: theme.mixins.pxToRem(12)
    }
  })
);

const TableContainerScroll = styled(TableContainer, {
  name,
  slot: 'tableContainerScroll'
})(({ theme }) => ({
  overflowX: 'auto',
  overflowY: 'visible',
  marginLeft: '375px',
  width: 'calc(100% - 375px)',
  cursor: 'pointer',
  '&::-webkit-scrollbar': {
    height: '6px'
  },
  '&::-webkit-scrollbar-thumb': {
    borderRadius: theme.shape.borderRadius,
    backgroundColor: 'rgba(0,0,0,.2)'
  },
  [theme.breakpoints.down('sm')]: {
    marginLeft: '40vw',
    width: 'calc(100% - 40vw)'
  }
}));

const RowTitle = styled(TableRow, { name, slot: 'RowTitle' })(({ theme }) => ({
  zIndex: '2',
  '& .MuiTableCell-root': {
    color: theme.palette.text.primary,
    fontSize: theme.mixins.pxToRem(15),
    fontWeight: theme.typography.fontWeightBold,
    height: theme.spacing(7),
    border: 'none',
    borderBottom: 'solid 1px',
    borderBottomColor: '#cecece',
    '&:first-of-type': {
      fontSize: theme.mixins.pxToRem(18),
      fontWeight: 'bold'
    }
  }
}));

const TableRowContent = styled(TableRow, { name, slot: 'TableRowContent' })(
  ({ theme }) => ({
    display: 'flex'
  })
);

const TitleBold = styled('p', { name, slot: 'TitleBold' })(({ theme }) => ({
  margin: theme.spacing(1, 0)
}));

type ValueComparisonPackage = {
  title: string;
  type: string;
  value: string;
};
const renderValueComparison = (data: ValueComparisonPackage) => {
  if (isEmpty(data)) return null;

  const { type, value } = data;

  if (type === 'no') return <LineIcon icon="ico-close" />;

  if (type === 'yes') return <LineIcon icon="ico-check" />;

  return (
    <Typography variant="body2" color="text.hint">
      {value}
    </Typography>
  );
};

const TableCellCustom = styled(TableCell, {
  name,
  slot: 'TableCellCustom',
  shouldForwardProp: prop => prop !== 'active' && prop !== 'cellVirtual'
})<{ active?: boolean; cellVirtual?: boolean }>(
  ({ theme, active, cellVirtual }) => ({
    color: theme.palette.text.hint,
    borderBottom: 'unset',
    ...(active && {
      background: theme.palette.action.hover
    }),
    ...(cellVirtual && {
      visibility: 'hidden',
      display: 'block !important',
      width: '0 !important ',
      minWidth: '0 !important',
      maxWidth: '0 !important',
      padding: '0 !important',
      margin: '0 !important'
    })
  })
);

const TableCellTitleHidden = styled(TableCell, {
  name,
  slot: 'TableCellTitleHidden'
})(({ theme }) => ({
  '&.MuiTableCell-root': {
    minWidth: '375px',
    maxWidth: '375px',
    marginLeft: '-375px',
    opacity: 0,
    width: '100%',
    paddingLeft: '0',
    [theme.breakpoints.down('sm')]: {
      minWidth: 'inherit',
      maxWidth: '40vw',
      marginLeft: '-40vw'
    }
  }
}));

const TableCellItemCustom = styled(TableCell, {
  name,
  slot: 'TableCellItemCustom',
  shouldForwardProp: prop => prop !== 'active'
})<{ active?: boolean }>(({ theme, active }) => ({
  '& p': {
    color: theme.palette.text.primary
  },
  alignItems: 'center',
  display: 'flex',
  ...(active && {
    background: theme.palette.action.hover
  })
}));

export default function ComparisonBlock({ title }: Props) {
  const { useFetchItems, usePageParams, i18n } = useGlobal();

  const pageParams = usePageParams();
  const dataSource = useResourceAction(
    APP_SUBSCRIPTION,
    RESOURCE_SUBSCRIPTION_COMPARISON,
    'viewAll'
  );
  const [items, loading] = useFetchItems({
    dataSource,
    pageParams,
    normalize: true,
    data: [],
    cache: true
  });
  const packagesData = useSelector(
    (state: GlobalState) =>
      state.subscription.entities.subscription_package || {}
  );
  const packages: SubscriptionPackageItemShape[] = !isEmpty(packagesData)
    ? Object.values(packagesData)
    : [];

  if (loading) {
    return <Skeleton width={'100%'} />;
  }

  if (isEmpty(packages) || isEmpty(items)) {
    return null;
  }

  return (
    <Block>
      <BlockHeader>
        <BlockTitle>{title}</BlockTitle>
      </BlockHeader>
      <BlockContent>
        <Box sx={{ position: 'relative' }}>
          <TableContainerScroll>
            <Table sx={{ width: '100%' }}>
              <TableCustom>
                <RowTitle>
                  <TableCellCustom>
                    <TitleBold>
                      {i18n.formatMessage({ id: 'features' })}
                    </TitleBold>
                  </TableCellCustom>
                  {packages.map(packageItem => (
                    <TableCellCustom
                      key={packageItem.id}
                      active={packageItem.is_purchased}
                    >
                      <TruncateText
                        lines={1}
                        variant="body1"
                        sx={{ maxWidth: '300px' }}
                        fontWeight={600}
                      >
                        {packageItem.title}
                      </TruncateText>
                    </TableCellCustom>
                  ))}
                </RowTitle>
                <Box>
                  {items.map(row => (
                    <TableRowContent key={row.id}>
                      <TableCellCustom>
                        <Typography fontWeight={500}>{row.title}</Typography>
                      </TableCellCustom>
                      <TableCellTitleHidden>
                        <Typography>{row.title}</Typography>
                      </TableCellTitleHidden>
                      {packages.map(packageItem => (
                        <TableCellItemCustom
                          key={`${row.id}${packageItem.id}`}
                          active={packageItem.is_purchased}
                        >
                          {renderValueComparison(row?.packages[packageItem.id])}
                        </TableCellItemCustom>
                      ))}
                    </TableRowContent>
                  ))}
                </Box>
              </TableCustom>
            </Table>
          </TableContainerScroll>
        </Box>
      </BlockContent>
    </Block>
  );
}
ComparisonBlock.displayName = 'ComparisonBlockBlock';

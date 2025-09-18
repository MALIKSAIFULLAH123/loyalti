import { APP_ACTIVITY } from '@metafox/activity-point';
import {
  BlockViewProps,
  useGlobal,
  useResourceAction
} from '@metafox/framework';
import {
  styled,
  TableContainer,
  Table,
  TableHead,
  TableRow,
  TableBody,
  TableCell
} from '@mui/material';
import tableCellClasses from '@mui/material/TableCell/tableCellClasses';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import React from 'react';
import ErrorBoundary from '@metafox/core/pages/ErrorPage/Page';

export type Props = BlockViewProps;

const TableStyled = styled(Table, { name: 'TableStyled' })(({ theme }) => ({
  minWidth: 700,
  borderSpacing: '0 8px',
  borderCollapse: 'separate'
}));

const StyledTableCell = styled(TableCell)(({ theme }) => ({
  [`&.${tableCellClasses.head}`]: {
    color: theme.palette.text.primary,
    fontWeight: theme.typography.fontWeightBold,
    fontSize: 15,
    borderRadius: theme.shape.borderRadius,
    borderBottom: 0
  },
  [`&.${tableCellClasses.body}`]: {
    fontSize: 15,
    color: theme.palette.text.secondary,
    marginBottom: theme.spacing(1),
    '&:last-child': {
      borderTopRightRadius: theme.shape.borderRadius,
      borderBottomRightRadius: theme.shape.borderRadius,
      backgroundColor: theme.palette.background.default
    },
    '&:first-of-type': {
      borderTopLeftRadius: theme.shape.borderRadius,
      borderBottomLeftRadius: theme.shape.borderRadius,
      backgroundColor: theme.palette.background.default
    }
  }
}));

const StyledTableRow = styled(TableRow)(({ theme }) => ({
  borderSpacing: '15px',
  borderCollapse: 'separate',
  backgroundColor: theme.palette.background.default,
  borderRadius: theme.shape.borderRadius
}));

export default function Base({
  title,
  emptyPage = 'core.block.no_content_with_icon',
  ...rest
}: Props) {
  const { useFetchDetail, usePageParams, i18n, jsxBackend } = useGlobal();
  const pageParams = usePageParams();

  const dataSource = useResourceAction(
    APP_ACTIVITY,
    'activitypoint_setting',
    'viewAll'
  );

  const [data, loading, error] = useFetchDetail({
    dataSource,
    pageParams: { id: pageParams.authId }
  });

  let content = null;

  const { packages } = Object.assign({}, data);

  if (packages?.length)
    content = (
      <>
        {packages.map((item, index) => (
          <TableStyled key={index}>
            <TableHead>
              <TableRow>
                <StyledTableCell>{item.action_label}</StyledTableCell>
                <StyledTableCell>
                  {i18n.formatMessage({ id: 'earned_points' })}
                </StyledTableCell>
                <StyledTableCell>
                  {i18n.formatMessage({ id: 'max_earned_points' })}
                </StyledTableCell>
                <StyledTableCell>
                  {i18n.formatMessage({ id: 'period_day' })}
                </StyledTableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {item.settings.map(row => (
                <StyledTableRow key={row}>
                  <StyledTableCell sx={{ width: '50%' }}>
                    {row.description}
                  </StyledTableCell>
                  <StyledTableCell>{row.points}</StyledTableCell>
                  <StyledTableCell>{row.max_earned}</StyledTableCell>
                  <StyledTableCell>{row.period}</StyledTableCell>
                </StyledTableRow>
              ))}
            </TableBody>
          </TableStyled>
        ))}
      </>
    );

  return (
    <Block testid="activityPointBlock" {...rest}>
      <BlockHeader title={title} />
      <BlockContent>
        <TableContainer sx={{ p: 2 }}>
          <ErrorBoundary
            loading={loading}
            error={error}
            emptyComponent={
              !packages?.length ? (
                <TableStyled>
                  {React.createElement(jsxBackend.get(emptyPage), {
                    image: 'ico-file-text-o',
                    description: i18n.formatMessage({
                      id: 'can_not_earn_points_at_this_time'
                    }),
                    title: i18n.formatMessage({ id: 'no_results_found' })
                  })}
                </TableStyled>
              ) : undefined
            }
          >
            {content}
          </ErrorBoundary>
        </TableContainer>
      </BlockContent>
    </Block>
  );
}

Base.displayName = 'ActivityPoint_HowToEarn';

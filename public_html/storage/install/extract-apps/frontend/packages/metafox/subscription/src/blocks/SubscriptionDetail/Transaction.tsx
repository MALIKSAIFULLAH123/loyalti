import React from 'react';
import {
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableRow,
  styled,
  Box
} from '@mui/material';
import { TruncateText, FormatDate } from '@metafox/ui';
import { isEmpty } from 'lodash';
type TableFieldProps = {
  label: string;
  value: string;
  isDate?: boolean;
};
type Props = {
  tableFields: TableFieldProps[];
  transactions: Record<string, any>;
};

const name = 'TransactionBlock';

const TableCustom = styled(TableBody, { name, slot: 'tableCustom' })(
  ({ theme }) => ({
    minWidth: 958,
    '& .MuiTableCell-root': {
      fontSize: theme.mixins.pxToRem(15),
      color: theme.palette.text.secondary,
      borderBottom: 0,
      minWidth: '200px',
      height: '56px',
      background: theme.palette.background.default,
      '&:first-of-type': {
        borderTopLeftRadius: theme.shape.borderRadius,
        borderBottomLeftRadius: theme.shape.borderRadius
      },
      '&:last-child': {
        borderTopRightRadius: theme.shape.borderRadius,
        borderBottomRightRadius: theme.shape.borderRadius
      }
    }
  })
);

const RowTitle = styled(TableRow, { name, slot: 'RowTitle' })(({ theme }) => ({
  '& .MuiTableCell-root': {
    color: theme.palette.text.primary,
    fontSize: theme.mixins.pxToRem(15),
    fontWeight: theme.typography.fontWeightBold,
    background: theme.palette.background.paper
  }
}));

export default function TransactionBlock({ tableFields, transactions }: Props) {
  if (isEmpty(tableFields) || isEmpty(transactions)) {
    return null;
  }

  return (
    <Box sx={{ position: 'relative' }}>
      <TableContainer>
        <Table
          sx={{
            width: '100%',
            borderCollapse: 'separate',
            borderSpacing: '0 8px'
          }}
        >
          <TableCustom>
            <RowTitle>
              {tableFields.map(tableField => (
                <TableCell key={tableField.value}>
                  <TruncateText
                    lines={1}
                    variant="body1"
                    sx={{ maxWidth: '300px' }}
                    fontWeight={600}
                  >
                    {tableField.label}
                  </TruncateText>
                </TableCell>
              ))}
            </RowTitle>
            {transactions.map(row => (
              <TableRow key={`r${row.id}`}>
                {tableFields.map(tableField => (
                  <TableCell key={`${row.id}${tableField.value}`}>
                    {tableField?.isDate ? (
                      <FormatDate
                        data-testid="publishedDate"
                        value={row[tableField.value]}
                        format="MMMM DD,YYYY HH:mm:ss"
                      />
                    ) : (
                      row[tableField.value]
                    )}
                  </TableCell>
                ))}
              </TableRow>
            ))}
          </TableCustom>
        </Table>
      </TableContainer>
    </Box>
  );
}
TransactionBlock.displayName = 'TransactionBlock';

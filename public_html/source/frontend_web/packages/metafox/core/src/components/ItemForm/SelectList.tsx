import * as React from 'react';
import { Box, ListItem, ListItemText, styled } from '@mui/material';
import { SelectPopper } from '@metafox/ui';
import { camelCase, isBoolean } from 'lodash';

const StyledListItem = styled(ListItem, { name: 'StyledListItem' })(
  ({ theme }) => ({
    borderBottom: 'solid 1px',
    borderBottomColor: theme.palette.border?.secondary,
    padding: `${theme.spacing(2)} 0`,
    display: 'flex',
    justifyContent: 'space-between',
    '&:first-of-type': {
      paddingTop: 0
    },
    '&:last-child': {
      paddingBottom: 0,
      borderBottom: 'none'
    }
  })
);

export type Props = {
  item: Record<string, any>;
  onChanged: (value: number | boolean, var_name: string) => void;
};

export function SelectItem({ item, onChanged }: Props) {
  const [value, setValue] = React.useState<number>(item?.value);

  const handleChange = evt => {
    const newValue = evt?.value;

    setValue(newValue);
    onChanged(
      isBoolean(newValue) ? newValue : parseInt(newValue, 10),
      item?.var_name
    );
  };

  return (
    <StyledListItem data-testid={camelCase(`field ${item.var_name}`)}>
      <ListItemText primary={item.phrase} sx={{ pr: 2, with: '50%' }} />
      <Box sx={{ width: '50%', textAlign: 'end' }}>
        <SelectPopper options={item?.options} value={value} handleChange={handleChange} />
      </Box>
    </StyledListItem>
  );
}

import {
  ListItem,
  Switch,
  SwitchProps,
  ListItemText,
  styled
} from '@mui/material';
import * as React from 'react';

const StyledSwitchList = styled(ListItem, { name: 'StyledSwitchList' })(
  ({ theme }) => ({
    borderBottom: 'solid 1px',
    borderBottomColor: theme.palette.border?.secondary,
    padding: `${theme.spacing(2)} 0 !important`,
    display: 'flex',
    justifyContent: 'space-between',
    '&:first-of-type': {
      paddingTop: 6
    },
    '&:last-child': {
      paddingBottom: 6,
      borderBottom: 'none'
    }
  })
);

type Props = {
  item: Record<string, any>;
  onChanged: (value: 0 | 1, var_name: string) => void;
  classes: any;
  color?: SwitchProps['color'];
  size?: SwitchProps['size'];
  disabled?: boolean;
};

export function SwitchList({
  item,
  onChanged,
  disabled,
  size = 'medium',
  color = 'primary'
}: Props) {
  const [value, setValue] = React.useState<boolean>(!!item.value);

  const handleChange = React.useCallback(() => {
    const newValue = !value;
    setValue(newValue);
    onChanged(newValue ? 1 : 0, item.var_name || item.module_id);
  }, [item.module_id, item.var_name, onChanged, value]);

  return (
    <StyledSwitchList>
      <ListItemText primary={item.phrase} sx={{ pr: 2 }} />
      <Switch
        size={size}
        checked={value}
        color={color}
        disabled={disabled}
        onChange={handleChange}
      />
    </StyledSwitchList>
  );
}

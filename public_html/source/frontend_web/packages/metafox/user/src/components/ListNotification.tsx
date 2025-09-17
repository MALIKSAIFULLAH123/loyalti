import { SwitchProps, ListItemText, styled, Box } from '@mui/material';
import * as React from 'react';
import { SwitchList } from '@metafox/core';
import { LineIcon } from '@metafox/ui';
import { useGlobal } from '@metafox/framework';

const StyledSwitchList = styled('div', {
  name: 'StyledSwitchList',
  shouldForwardProp: (prop: string) => prop !== 'open'
})<{ open: boolean }>(({ theme, open }) => ({
  borderBottom: 'solid 1px',
  borderBottomColor: theme.palette.border?.secondary,
  padding: `${theme.spacing(open ? 3 : 1)} 0 !important`,
  '&:first-of-type': {
    paddingTop: 6
  },
  '&:last-child': {
    paddingBottom: 6,
    borderBottom: 'none'
  }
}));

const AppNameStyled = styled('div', { name: 'AppNameStyled' })(({ theme }) => ({
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',
  cursor: 'pointer'
}));

const AppNameText = styled(ListItemText, { name: 'AppNameText' })(
  ({ theme }) => ({
    '& span': {
      fontWeight: theme.typography.fontWeightBold
    }
  })
);

type Props = {
  typeAction?: string;
  item: Record<string, any>;
  classes?: any;
  color?: SwitchProps['color'];
  size?: SwitchProps['size'];
  disabled?: boolean;
};

const AppSettings = styled('div', {
  name: 'appSettings',
  shouldForwardProp: (prop: string) => prop !== 'open'
})<{ open: boolean }>(({ theme, open }) => ({
  marginLeft: theme.spacing(2),
  ...(open && {
    '& div': {
      border: 0
    }
  })
}));

export default function ListNotification({
  item,
  typeAction,
  disabled,
  size = 'medium',
  color = 'primary'
}: Props) {
  const { dispatch } = useGlobal();
  const [open, setOpen] = React.useState<boolean>(false);
  const [value, setValue] = React.useState<boolean>(!!item.value);

  const { channel, type, app_name } = item;

  const onChanged = (value: 0 | 1, var_name: string) => {
    dispatch({
      type: `setting/${typeAction}/UPDATE`,
      payload: { var_name, value, channel }
    });
  };

  const onChangeSettingApp = (value: 0 | 1, module_id: string) => {
    dispatch({
      type: `setting/${typeAction}/UPDATE`,
      payload: { channel, value, module_id }
    });
    setValue(pre => !pre);
  };

  const handleChange = () => {
    setOpen(pre => !pre);
  };

  return (
    <StyledSwitchList open={!open}>
      <AppNameStyled onClick={handleChange}>
        <AppNameText primary={app_name} sx={{ pr: 2 }} />
        <LineIcon icon={open ? 'ico-angle-up' : 'ico-angle-down'} />
      </AppNameStyled>
      {open && (
        <AppSettings ml={2} open={!value}>
          <SwitchList onChanged={onChangeSettingApp} item={item} />
          <Box ml={2}>
            {value &&
              type.map(typeItem => (
                <SwitchList
                  onChanged={onChanged}
                  item={typeItem}
                  key={typeItem.var_name}
                />
              ))}
          </Box>
        </AppSettings>
      )}
    </StyledSwitchList>
  );
}

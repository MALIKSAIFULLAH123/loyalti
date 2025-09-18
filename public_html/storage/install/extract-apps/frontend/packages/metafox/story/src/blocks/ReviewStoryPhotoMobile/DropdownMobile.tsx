import { useGlobal } from '@metafox/framework';
import { Box, Popover, styled, MenuItem } from '@mui/material';
import { isEmpty } from 'lodash';
import React from 'react';

const AddTextStyled = styled(Box)(({ theme }) => ({
  width: '40px',
  height: '40px',
  padding: theme.spacing(1.25),
  borderRadius: '50%',
  backgroundColor: theme.palette.background.default,
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
  '& span': {
    fontSize: theme.mixins.pxToRem(16),
    fontWeight: theme.typography.fontWeightSemiBold
  }
}));

const WrapperButtonAddText = styled(Box)(({ theme }) => ({}));

type ConfigProps = {
  item: any;
  updateItem: (data) => void;
  option: any[];
};

const DropdownMobile = (config: ConfigProps) => {
  const { i18n } = useGlobal();
  const [open, setOpen] = React.useState<boolean>(false);
  const controlRef = React.useRef();

  const { option, updateItem, item } = config || {};

  if (isEmpty(option)) return;

  const handleChangeFontStyle = item => {
    updateItem && updateItem({ fontFamily: item.value });
    setOpen(false);
  };

  return (
    <>
      <WrapperButtonAddText ref={controlRef}>
        <AddTextStyled onClick={() => setOpen(true)}>
          <span>{i18n.formatMessage({ id: 'Aa' })}</span>
        </AddTextStyled>
      </WrapperButtonAddText>

      <Popover
        disablePortal={false}
        open={open}
        anchorEl={controlRef.current}
        onClose={() => setOpen(false)}
        style={{ zIndex: 9999 }}
        anchorOrigin={{
          vertical: 'center',
          horizontal: 'left'
        }}
        transformOrigin={{
          vertical: 'top',
          horizontal: 'right'
        }}
      >
        <Box width={160}>
          {option.map(fontstyle => (
            <MenuItem
              value={fontstyle.value}
              key={fontstyle.value}
              onClick={() => handleChangeFontStyle(fontstyle)}
            >
              <Box
                sx={
                  item?.fontFamily === fontstyle.value && {
                    fontWeight: 'bold'
                  }
                }
              >
                {fontstyle.label}
              </Box>
            </MenuItem>
          ))}
        </Box>
      </Popover>
    </>
  );
};

export default DropdownMobile;

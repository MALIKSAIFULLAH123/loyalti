import { ClickOutsideListener, LineIcon, Popper } from '@metafox/ui';
import {
  Box,
  Button,
  MenuItem,
  Paper,
  Typography,
  styled
} from '@mui/material';
import React from 'react';
import { useGlobal } from '@metafox/framework';
import { alpha } from '@mui/system/colorManipulator';
import { camelCase } from 'lodash';

const PaperMenu = styled(Paper, {
  name: 'MuiActionMenu-menu'
})<{}>(({ theme }) => ({
  padding: theme.spacing(1, 0)
}));

const MenuItemStyled = styled(MenuItem, {
  name: 'MenuItem',
  shouldForwardProp: props => props !== 'actived' && props !== 'hasDescription'
})<{ actived?: boolean; hasDescription?: boolean }>(
  ({ theme, actived, hasDescription }) => ({
    ...(actived && {
      backgroundColor: alpha(theme.palette.primary.main, 0.08)
    }),
    ...(hasDescription && {
      display: 'flex',
      flexDirection: 'column',
      alignItems: 'flex-start'
    }),
    '&:hover': {
      backgroundColor: 'rgba(0, 0, 0, 0.04)'
    }
  })
);

export interface SelectProps {
  disabled?: boolean;
  value?: number;
  item?: any;
  handleChange?: any;
  optionConfig?: any;
  options?: Array<any>;
  sx?: any;
  name?: string;
}

const SelectStage = styled('div', {
  name: 'SelectPopper',
  slot: 'SelectStage'
})(({ theme }) => ({
  display: 'flex',
  justifyContent: 'flex-end'
}));

const ButtonStyled = styled(Button, {
  name: 'SelectPopper',
  slot: 'ButtonStyled'
})(({ theme }) => ({
  height: 'auto',
  textAlign: 'right'
}));

export default function SelectPopper({
  value,
  optionConfig = {},
  disabled,
  handleChange,
  options,
  sx,
  name
}: SelectProps) {
  const { i18n } = useGlobal();
  const anchorRef = React.useRef<HTMLButtonElement>();
  const [open, setOpen] = React.useState<boolean>(false);
  const [stateValue, setStateValue] = React.useState(
    options?.find(x => x.value === value) || options[0]
  );

  const ref = React.useRef();

  React.useEffect(() => {
    const opt = options.find(x => x.value === value);

    if (opt) {
      setStateValue(opt);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [value]);

  const handleClose = () => {
    setOpen(false);
  };

  const handleClick = () => {
    if (!disabled) setOpen(prev => !prev);
  };

  const handleClickMenu = (item: any) => {
    handleClose();
    handleChange(item);
  };

  return (
    <Box ref={ref} sx={{ overflow: 'hidden' }}>
      <SelectStage>
        <ButtonStyled
          variant="text"
          ref={anchorRef}
          onClick={handleClick}
          disabled={disabled}
          disableRipple
          sx={sx}
          data-testid={camelCase(`select ${name}`)}
        >
          <Typography
            variant="body1"
            color="text.secondary"
            sx={{
              display: 'flex',
              alignItems: 'center',
              '> span': { marginLeft: '8px' }
            }}
          >
            {i18n.formatMessage({ id: stateValue.label })}
            <LineIcon icon="ico-caret-down" />
          </Typography>
        </ButtonStyled>
      </SelectStage>
      <ClickOutsideListener onClickAway={handleClose} excludeRef={anchorRef}>
        <Popper
          data-testid="selectPopper"
          anchorEl={anchorRef.current}
          open={Boolean(open)}
          style={{ zIndex: 1000 }}
          placement="bottom-end"
        >
          <PaperMenu>
            {options
              ? options.map((item, index) => (
                  <MenuItemStyled
                    value={item.value}
                    data-value={item.value}
                    key={index.toString()}
                    onClick={() => handleClickMenu(item)}
                    actived={value === item.value}
                    hasDescription={item?.description}
                  >
                    <Typography
                      variant="body1"
                      {...(optionConfig?.labelProps || {})}
                    >
                      {i18n.formatMessage({ id: item.label })}
                    </Typography>
                    {item.description ? (
                      <Typography
                        variant="body2"
                        color="text.secondary"
                        {...(optionConfig?.descriptionProps || {})}
                        mt={0.5}
                      >
                        {item.description}
                      </Typography>
                    ) : null}
                  </MenuItemStyled>
                ))
              : null}
          </PaperMenu>
        </Popper>
      </ClickOutsideListener>
    </Box>
  );
}

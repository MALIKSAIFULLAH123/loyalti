import { useGlobal } from '@metafox/framework';
import ColorPicker from '@metafox/story/components/ColorPicker/ColorPicker';
import {
  Box,
  Select,
  styled,
  MenuItem,
  Typography,
  Tooltip,
  SliderValueLabelProps,
  Slider,
  Stack
} from '@mui/material';
import React from 'react';

const Root = styled(Box)(({ theme }) => ({}));

interface Props {
  optionFontStyle: any[];
  item: any;
  updateItem: (data) => void;
}

function ValueLabelComponent(props: SliderValueLabelProps) {
  const { children, value } = props;

  return (
    <Tooltip enterTouchDelay={0} placement="top" title={value}>
      {children}
    </Tooltip>
  );
}

const MenuTextItem = function MenuTextItem({
  optionFontStyle,
  item,
  updateItem
}: Props) {
  const { i18n } = useGlobal();

  if (!item) return null;

  const handleBlur = () => {};

  const handleChangeFontStyle = e => {
    updateItem && updateItem({ fontFamily: e.target.value });
  };

  const handleChangeFontSize = e => {
    updateItem && updateItem({ fontSize: e.target.value });
  };

  return (
    <Root>
      <Box>
        <Typography gutterBottom>
          {i18n.formatMessage({ id: 'text_size' })}
        </Typography>
        <Stack spacing={1.5} direction="row" sx={{ mb: 1 }} alignItems="center">
          <Slider
            valueLabelDisplay="auto"
            slots={{
              valueLabel: ValueLabelComponent
            }}
            aria-label="custom thumb label"
            defaultValue={12}
            value={item?.fontSize}
            onChange={handleChangeFontSize}
            min={12}
            max={80}
          />
          <span>{item?.fontSize}</span>
        </Stack>
      </Box>
      {optionFontStyle ? (
        <Select
          onBlur={handleBlur}
          value={item?.fontFamily}
          displayEmpty
          defaultValue={optionFontStyle[0]?.value}
          onChange={handleChangeFontStyle}
          id={'select-font-style'}
          inputProps={{
            size: 'medium',
            fullWidth: 1
          }}
          data-testid={'select-font-style'}
          sx={{ width: '100%', mb: 2 }}
        >
          {optionFontStyle.map((option, key) => (
            <MenuItem value={option.value} key={key}>
              {option.label}
            </MenuItem>
          ))}
        </Select>
      ) : null}
      <ColorPicker
        defaultValue={item.color}
        item={item}
        updateItem={updateItem}
      />
    </Root>
  );
};

export default MenuTextItem;

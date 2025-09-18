import ColorPickerMobile from '@metafox/story/components/ColorPicker/ColorPickerMobile';
import {
  Box,
  styled,
  Tooltip,
  SliderValueLabelProps,
  Slider,
  Stack
} from '@mui/material';
import React from 'react';
import DropdownMobile from './DropdownMobile';
import { LineIcon } from '@metafox/ui';
import AddLinkButton from '../ReviewStoryBackgroundMobile/AddLinkButton';

const Root = styled(Box)(({ theme }) => ({
  width: '100%',
  height: '100%',
  position: 'absolute'
}));

const SliderWrapper = styled(Box)(({ theme }) => ({
  position: 'absolute',
  top: '20%',
  left: 0,
  zIndex: 2
}));

const WrapperAction = styled(Box)(({ theme }) => ({
  position: 'absolute',
  top: 100,
  right: 16,
  zIndex: 2,
  '& > div': {
    marginBottom: theme.spacing(2)
  }
}));

const WrapperColor = styled(Box)(({ theme }) => ({
  zIndex: 2,
  border: theme.mixins.border('secondary'),
  borderRadius: '50%'
}));

const SliderStyled = styled(Slider)(({ theme }) => ({
  color: '#fff',
  '& .MuiSlider-rail': {
    width: 0,
    height: 0,
    borderLeft: '10px solid transparent',
    borderRight: '10px solid transparent',
    borderTop: '200px solid currentColor',
    backgroundColor: 'transparent',
    borderRadius: 0
  },
  '& .MuiSlider-track': {
    border: 'none',
    backgroundColor: 'transparent'
  }
}));

const DeleteIconStyled = styled(Box)(({ theme }) => ({
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

interface Props {
  optionFontStyle: any[];
  item: any;
  updateItem: (data) => void;
  itemSelect?: any;
  handleDeleteText?: any;
  nameFieldExpandLink?: string;
  updateExpandData?: (data) => void;
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
  itemSelect,
  optionFontStyle,
  item,
  updateItem,
  handleDeleteText,
  nameFieldExpandLink,
  updateExpandData
}: Props) {
  if (!item) return null;

  const handleChangeFontSize = e => {
    updateItem && updateItem({ fontSize: e.target.value });
  };

  return (
    <Root>
      <SliderWrapper>
        <Stack direction="row" sx={{ height: 200 }} alignItems="center">
          <SliderStyled
            orientation="vertical"
            valueLabelDisplay="auto"
            slots={{
              valueLabel: ValueLabelComponent
            }}
            aria-label="custom thumb label"
            defaultValue={12}
            value={itemSelect?.fontSize || item?.fontSize}
            onChange={handleChangeFontSize}
            min={12}
            max={80}
          />
        </Stack>
      </SliderWrapper>

      <WrapperAction>
        <WrapperColor>
          <ColorPickerMobile
            defaultValue={itemSelect?.color || item.color}
            item={item}
            updateItem={updateItem}
          />
        </WrapperColor>
        {optionFontStyle ? (
          <DropdownMobile
            option={optionFontStyle}
            updateItem={updateItem}
            item={itemSelect || item}
          />
        ) : null}
        {nameFieldExpandLink && (
          <AddLinkButton
            updateItem={updateExpandData}
            item={item}
            nameField={nameFieldExpandLink}
          />
        )}
        <DeleteIconStyled onClick={() => handleDeleteText(itemSelect)}>
          <LineIcon icon="ico-trash-alt-o" />
        </DeleteIconStyled>
      </WrapperAction>
    </Root>
  );
};

export default MenuTextItem;

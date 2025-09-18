import { DEFAULT_FONTSIZE } from '@metafox/story/constants';
import {
  Box,
  styled,
  Tooltip,
  SliderValueLabelProps,
  Slider,
  Stack,
  Collapse,
  Paper,
  IconButton,
  Typography
} from '@mui/material';
import React from 'react';
import DropdownMobile from '../ReviewStoryPhotoMobile/DropdownMobile';
import { LineIcon } from '@metafox/ui';
import { ScrollContainer } from '@metafox/layout';
import { useGlobal } from '@metafox/framework';
import { isEmpty } from 'lodash';
import AddLinkButton from './AddLinkButton';

const name = 'BackgroundPicker';

const Root = styled(Box)(({ theme }) => ({
  width: '100%',
  height: '100%',
  position: 'absolute',
  top: 0,
  left: 0,
  right: 0
}));

const BackgroundPickerAdornment = styled(Box, {
  name: 'MuiButton',
  slot: 'ColorPickerAdornment'
})<{ background?: string }>(({ theme, background }) => ({
  width: '40px',
  height: '40px',
  borderRadius: theme.shape.borderRadius,
  backgroundImage: `url(${background})`,
  backgroundRepeat: 'no-repeat',
  backgroundSize: 'cover',
  backgroundPosition: 'center',
  cursor: 'pointer',
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
  ' span': {
    color: '#000',
    fontSize: theme.mixins.pxToRem(24)
  }
}));

const SliderWrapper = styled(Box)(({ theme }) => ({
  position: 'absolute',
  top: '20%',
  left: 0,
  zIndex: 2
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
    borderRadius: 0,
    opacity: '0.75'
  },
  '& .MuiSlider-track': {
    border: 'none',
    backgroundColor: 'transparent'
  }
}));

const FormViewDetail = styled(Paper)(({ theme }) => ({
  position: 'absolute',
  bottom: 0,
  width: '100%',
  height: '75%',
  borderRadius: 0,
  backgroundColor: theme.palette.background.paper,
  padding: theme.spacing(2),
  paddingTop: theme.spacing(2.5),
  display: 'flex',
  flexDirection: 'column',
  zIndex: 99
}));

const HeaderBlock = styled(Box, { name, slot: 'HeaderBlock' })(({ theme }) => ({
  padding: theme.spacing(2),
  paddingTop: 0,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center'
}));
const HeaderTitle = styled(Box, { name, slot: 'HeaderTitle' })(() => ({}));
const ContentBlock = styled(Box, { name, slot: 'ContentBlock' })(
  ({ theme }) => ({
    borderTop: theme.mixins.border('secondary'),
    overflow: 'hidden'
  })
);

const CloseButton = styled(IconButton, { name })(() => ({
  marginRight: 'auto',
  transform: 'translate(4px,0)',
  position: 'absolute',
  right: '16px'
}));

const Container = styled(Box, { slot: 'ContetnWrapper' })(({ theme }) => ({
  paddingTop: theme.spacing(2)
}));

const ContentWrapper = styled(Box, { slot: 'ContetnWrapper' })(({ theme }) => ({
  display: 'flex',
  flexDirection: 'row',
  flexWrap: 'wrap'
}));

const ImageItem = styled(Box, {
  slot: 'ImageItem',
  shouldForwardProp: props => props !== 'active' && props !== 'disabled'
})<{ active?: boolean; disabled?: boolean }>(({ theme, active, disabled }) => ({
  height: '40px',
  width: '40px',
  borderRadius: theme.shape.borderRadius / 2,
  margin: theme.spacing(1),
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
  ...(active && {
    '&:before': {
      content: '""',
      position: 'absolute',
      height: '42px',
      width: '42px',
      border: theme.mixins.border('primary'),
      borderWidth: '3px',
      ...(theme.palette.mode === 'dark' && {
        borderColor: '#fff'
      }),
      borderRadius: theme.shape.borderRadius / 2
    }
  }),
  backgroundRepeat: 'no-repeat',
  backgroundSize: 'cover',
  backgroundPosition: 'center',
  cursor: disabled ? 'not-allowed' : 'pointer'
}));

const WrapperAction = styled(Box)(({ theme }) => ({
  position: 'absolute',
  top: 16,
  right: 16,
  zIndex: 2,
  '& > div': {
    marginBottom: theme.spacing(2)
  }
}));

const WrapperBackground = styled(Box)(({ theme }) => ({
  zIndex: 2,
  border: theme.mixins.border('secondary'),
  borderRadius: theme.shape.borderRadius
}));

const WrapperBox = styled(Box)(({ theme }) => ({
  position: 'absolute',
  bottom: 0,
  right: 0,
  width: '100%',
  height: '100%',
  zIndex: 98
}));

interface Props {
  item: any;
  updateItem: (data) => void;
  optionFontStyle?: any[];
  optionBackground?: any[];
  hiddenActionText?: boolean;
  setHiddenActionText?: any;
  nameFieldExpandLink?: string;
}

function ValueLabelComponent(props: SliderValueLabelProps) {
  const { children, value } = props || {};

  return (
    <Tooltip enterTouchDelay={0} placement="top" title={value}>
      {children}
    </Tooltip>
  );
}

const MenuTextItem = ({
  item,
  updateItem,
  optionFontStyle,
  optionBackground = [],
  hiddenActionText = false,
  setHiddenActionText = () => {},
  nameFieldExpandLink
}: Props) => {
  const { i18n } = useGlobal();
  const [openBackground, setOpenBackground] = React.useState<boolean>(false);

  const handleChangeFontSize = e => {
    updateItem && updateItem({ size: e.target.value });
  };

  const handleChangeBackground = item => {
    updateItem &&
      updateItem({ background_id: item?.id, background: item?.value });
  };

  const backgroundSrc = optionBackground?.find(
    op => op.id === item?.background_id
  )?.label;

  return (
    <Root>
      {hiddenActionText || openBackground ? null : (
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
              value={item.size || DEFAULT_FONTSIZE}
              onChange={handleChangeFontSize}
              min={12}
              max={80}
            />
          </Stack>
        </SliderWrapper>
      )}
      <WrapperAction>
        {isEmpty(optionBackground) ? null : (
          <WrapperBackground>
            <BackgroundPickerAdornment
              background={backgroundSrc}
              onClick={() => {
                setOpenBackground(true);
                setHiddenActionText(true);
              }}
            />
          </WrapperBackground>
        )}
        {hiddenActionText ||
        openBackground ||
        isEmpty(optionFontStyle) ? null : (
          <DropdownMobile
            option={optionFontStyle}
            updateItem={updateItem}
            item={item}
          />
        )}
        {nameFieldExpandLink && (
          <AddLinkButton
            updateItem={updateItem}
            item={item}
            nameField={nameFieldExpandLink}
          />
        )}
      </WrapperAction>
      <Collapse in={openBackground} orientation="vertical">
        <WrapperBox>
          <FormViewDetail data-testid="content-viewDetail">
            <HeaderBlock>
              <HeaderTitle>
                <Typography variant="h4" color={'text.primary'}>
                  {i18n.formatMessage({ id: 'background' })}
                </Typography>
              </HeaderTitle>
              <CloseButton
                size="small"
                onClick={() => {
                  setOpenBackground(false);
                  setHiddenActionText(false);
                }}
                data-testid="buttonClose"
                role="button"
              >
                <LineIcon icon="ico-close" />
              </CloseButton>
            </HeaderBlock>
            <ContentBlock>
              <ScrollContainer autoHide autoHeight autoHeightMax={'100%'}>
                <Container>
                  <ContentWrapper>
                    {optionBackground.map((background, key) => (
                      <ImageItem
                        key={background.id}
                        active={background.id === item?.background_id}
                        onClick={() => handleChangeBackground(background)}
                        style={{ backgroundImage: `url(${background?.label})` }}
                      />
                    ))}
                  </ContentWrapper>
                </Container>
              </ScrollContainer>
            </ContentBlock>
          </FormViewDetail>
        </WrapperBox>
      </Collapse>
    </Root>
  );
};

export default MenuTextItem;

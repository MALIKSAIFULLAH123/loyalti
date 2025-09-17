import React from 'react';
import {
  Box,
  FormControlLabel,
  Paper,
  Popper,
  Radio,
  RadioGroup,
  Tooltip,
  Typography,
  styled,
  MenuItem
} from '@mui/material';
import { CheckCircleOutline, RadioButtonUnchecked } from '@mui/icons-material';
import LineIcon from '../LineIcon';
import { useGlobal } from '@metafox/framework';
import { isEmpty, isFunction } from 'lodash';
import { ClickOutsideListener } from '@metafox/ui';
interface ControlProps {
  [key: string]: any;
}

const name = 'control-video';

const IconButtonStyled = styled('div', { name, slot: 'icon-button' })(
  ({ theme }) => ({
    color: '#fff !important',
    fontSize: theme.mixins.pxToRem(15),
    '&:hover': {
      cursor: 'pointer'
    }
  })
);

const ButtonWrapper = styled(Box, { name, slot: 'icon-button' })(
  ({ theme }) => ({
    padding: theme.spacing(1),
    position: 'relative'
  })
);
const PlayBackRateWrapper = styled(Box, {
  name,
  slot: 'PlayBackRateWrapper'
})(({ theme }) => ({
  display: 'block'
}));

const MenuWrapper = styled(Paper, {
  name,
  slot: 'MenuWrapper'
})(({ theme }) => ({
  padding: theme.spacing(0.5, 0),
  minWidth: '180px'
}));

const MenuItemWrapper = styled(MenuItem, {
  name,
  slot: 'MenuItemWrapper'
})(({ theme }) => ({
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',
  minWidth: '180px'
}));

const PopperStyled = styled(Popper, { name: 'PopperStyled' })(({ theme }) => ({
  '& .MuiPopper-paper': {
    borderRadius: theme.shape.borderRadius / 2
  }
}));

const TitleItemMenu = styled(Box, { name: 'TitleItemMenu' })(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  overflow: 'hidden',
  '&:hover': {
    cursor: 'pointer'
  }
}));

const speechData = [0.5, 1, 1.5, 2];

const PlaybackSpeedComponent = props => {
  const { i18n } = useGlobal();

  const { onPlaybackRateChange, playRate, backMenuItem } = props || {};

  return (
    <PlayBackRateWrapper p={1}>
      <TitleItemMenu
        mb={1}
        onClick={() => isFunction(backMenuItem) && backMenuItem()}
      >
        <LineIcon icon="ico-angle-left" sx={{ mr: 1, fontWeight: '500' }} />
        <Typography component="div" variant="h5" fontWeight={500}>
          {i18n.formatMessage({ id: 'player_playback_speed' })}
        </Typography>
      </TitleItemMenu>
      <RadioGroup
        value={playRate.toString()}
        onChange={(e, value) => {
          onPlaybackRateChange(parseFloat(value));
        }}
        sx={{ ml: 1.5 }}
      >
        {speechData.map(item => (
          <FormControlLabel
            key={item.toString()}
            value={item.toString()}
            style={{ width: 'fit-content' }}
            control={
              <Radio
                sx={{ p: 0.75 }}
                color="primary"
                size="small"
                icon={
                  item === playRate ? (
                    <CheckCircleOutline fontSize="small" />
                  ) : (
                    <RadioButtonUnchecked fontSize="small" />
                  )
                }
              />
            }
            label={item}
            className="option"
          />
        ))}
      </RadioGroup>
    </PlayBackRateWrapper>
  );
};

const SettingControl = (props: ControlProps) => {
  const { playRate, setIsEditControl } = props || {};
  const { i18n } = useGlobal();
  const [openRatePlayback, setOpenRatePlayback] = React.useState(false);
  const anchorRateRef = React.useRef<HTMLDivElement>();
  const [itemSelect, setItemSelect] = React.useState(null);
  const containerRef = React.useRef<HTMLDivElement>();

  const handleOpenPlaybackRate = e => {
    e.stopPropagation();

    setOpenRatePlayback(prev => !prev);
    setItemSelect(null);
  };

  const backMenuItem = () => {
    setItemSelect(null);
  };

  React.useEffect(() => {
    setIsEditControl && setIsEditControl(openRatePlayback);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [openRatePlayback]);

  const menu = [
    {
      id: 'playback_speed',
      label: 'player_playback_speed',
      value: playRate,
      component: props => (
        <PlaybackSpeedComponent {...props} backMenuItem={backMenuItem} />
      )
    }
  ];

  const handleSelectItemMenu = item => {
    if (isEmpty(item)) return;

    setItemSelect(item);
  };

  return (
    <ButtonWrapper ref={containerRef}>
      <Tooltip
        title={i18n.formatMessage({
          id: 'settings'
        })}
      >
        <IconButtonStyled ref={anchorRateRef} onClick={handleOpenPlaybackRate}>
          <LineIcon icon="ico-gear-o" />
        </IconButtonStyled>
      </Tooltip>
      <ClickOutsideListener
        onClickAway={() => setOpenRatePlayback(false)}
        excludeRef={anchorRateRef}
      >
        <PopperStyled
          open={openRatePlayback}
          anchorEl={anchorRateRef.current}
          onClose={() => setOpenRatePlayback(false)}
          anchorOrigin={{
            vertical: 'top',
            horizontal: 'center'
          }}
          transformOrigin={{
            vertical: 'bottom',
            horizontal: 'center'
          }}
          placement="top"
          anchorPosition={{ top: 60, left: 0 }}
          disablePortal
          disableScrollLock
        >
          <MenuWrapper>
            {itemSelect
              ? itemSelect.component(props)
              : menu
                  ?.filter(item => item.id)
                  ?.map(item => (
                    <MenuItemWrapper
                      key={item?.id}
                      onClick={() => handleSelectItemMenu(item)}
                    >
                      <Box sx={{ flex: 1, overflow: 'hidden' }}>
                        <Typography
                          component="div"
                          variant="body1"
                          fontWeight={500}
                        >
                          {i18n.formatMessage({
                            id: item?.label || 'setting'
                          })}
                        </Typography>
                      </Box>
                      <Typography sx={{ textAlign: 'end' }}>
                        {item?.value}
                      </Typography>
                      <LineIcon icon="ico-angle-right" sx={{ ml: 0.5 }} />
                    </MenuItemWrapper>
                  ))}
          </MenuWrapper>
        </PopperStyled>
      </ClickOutsideListener>
    </ButtonWrapper>
  );
};

export default SettingControl;

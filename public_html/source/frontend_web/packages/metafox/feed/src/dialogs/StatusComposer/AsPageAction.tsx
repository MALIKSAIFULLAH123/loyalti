import { useGlobal } from '@metafox/framework';
import { LineIcon, UserAvatar, TruncateText } from '@metafox/ui';
import {
  Box,
  Button,
  MenuItem,
  Popover,
  styled,
  Typography
} from '@mui/material';
import React from 'react';

export interface AsPageControlProps {
  page: Record<string, any>;
  asPage: boolean;
  setAsPage: (val: boolean) => void;
}

const AvatarWrapper = styled('div', { name: 'AvatarWrapper' })(({ theme }) => ({
  marginRight: theme.spacing(1.5)
}));

const OptionContent = styled('div', { name: 'OptionContent' })(({ theme }) => ({
  flex: 1,
  minWidth: 0,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'space-between'
}));

const IconCheck = styled('div', { name: 'IconCheck' })<{ active?: boolean }>(
  ({ active, theme }) => ({
    marginLeft: theme.spacing(2),
    color: theme.palette.primary.main,
    opacity: `${active ? 1 : 0}`
  })
);

function AsPageControl({
  page,
  asPage,
  setAsPage,
  children
}: AsPageControlProps) {
  const anchorRef = React.useRef<HTMLButtonElement>();
  const [open, setOpen] = React.useState<boolean>(false);
  const { useSession, i18n } = useGlobal();
  const { user: authUser } = useSession();

  const options = [
    {
      value: false,
      data: authUser
    },
    {
      value: true,
      data: page
    }
  ];
  const selectedData = options.find(x => x.value === asPage);

  const handleClose = () => {
    setOpen(false);
  };

  const handleClick = () => {
    setOpen(true);
  };

  const handleClickMenu = (item: any) => {
    handleClose();
    setAsPage(item.value);
  };

  return (
    <>
      <UserAvatar
        sx={{ mr: 1 }}
        noLink
        user={selectedData.data}
        size={48}
        data-testid="userAvatar"
        onClick={handleClick}
      />
      <Box>
        <Button
          variant="text"
          color="primary"
          size="smaller"
          ref={anchorRef}
          onClick={handleClick}
          data-testid={'buttonAsPage'}
          endIcon={<LineIcon icon="ico-caret-down" />}
          sx={{ color: theme => theme.palette.text.primary }}
        >
          <Box sx={{ display: 'flex', alignItems: 'center' }}>
            <TruncateText lines={1} variant="h5" color="text.primary">
              {selectedData.data.title ?? selectedData.data.full_name}
            </TruncateText>
          </Box>
        </Button>
        {children}
      </Box>
      <Popover
        disablePortal
        id={open ? 'aspage-popover' : undefined}
        open={Boolean(open)}
        anchorEl={anchorRef.current}
        onClose={handleClose}
        anchorOrigin={{ vertical: 'bottom', horizontal: 'left' }}
        transformOrigin={{ vertical: 'top', horizontal: 'left' }}
      >
        <Box component="div" data-testid="menuAsPage">
          {options
            ? options.map((item, index) => (
                <MenuItem
                  sx={{ px: 2, py: 1 }}
                  key={index.toString()}
                  onClick={() => handleClickMenu(item)}
                >
                  <Box
                    sx={{
                      display: 'flex',
                      alignItems: 'center',
                      width: '100%'
                    }}
                  >
                    <AvatarWrapper>
                      <UserAvatar user={item.data} size={40} noLink />
                    </AvatarWrapper>
                    <OptionContent>
                      <div>
                        <Typography
                          component="div"
                          variant="h5"
                          color="textPrimary"
                          sx={{ mb: 0.25 }}
                        >
                          {item.data.title ?? item.data.full_name}
                        </Typography>
                        <TruncateText
                          variant={'body2'}
                          lines={1}
                          component="div"
                          color="text.hint"
                        >
                          {i18n.formatMessage({
                            id: item.value ? 'page_admin' : 'personal'
                          })}
                        </TruncateText>
                      </div>
                      <IconCheck active={asPage === item.value}>
                        <LineIcon icon={'ico-check'} />
                      </IconCheck>
                    </OptionContent>
                  </Box>
                </MenuItem>
              ))
            : null}
        </Box>
      </Popover>
    </>
  );
}

export default AsPageControl;

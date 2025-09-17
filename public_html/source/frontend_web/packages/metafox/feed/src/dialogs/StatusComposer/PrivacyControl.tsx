import { useGlobal } from '@metafox/framework';
import { ClickOutsideListener, LineIcon, Popper, Tooltip } from '@metafox/ui';
import { Button, MenuItem, Paper, styled } from '@mui/material';
import React from 'react';

const PaperMenu = styled(Paper, {
  name: 'MuiActionMenu-menu'
})<{}>(({ theme }) => ({
  padding: theme.spacing(1, 0),
  margin: theme.spacing(0.5, 0)
}));

export interface PrivacyControlProps {
  showLabel?: boolean;
  showDropdown?: boolean;
  label?: string;
  disabled?: boolean;
  value?: any;
  item?: any;
  setValue?: (value: unknown) => void;
  feed?: any;
}

enum Privacy {
  Everyone = 0,
  Friends = 2,
  Friends_of_friends = 3,
  Only_me = 4,
  Custom = 10,
  Community = 1
}

const options = [
  {
    label: 'public',
    value: Privacy.Everyone,
    icon: 'ico-globe'
  },
  {
    label: 'community',
    value: Privacy.Community,
    icon: 'ico-user-circle'
  },
  {
    label: 'friends',
    value: Privacy.Friends,
    icon: 'ico-user-two-men'
  },
  {
    label: 'friends_of_friends',
    value: Privacy.Friends_of_friends,
    icon: 'ico-user-man-three'
  },
  {
    label: 'only_me',
    value: Privacy.Only_me,
    icon: 'ico-lock'
  },
  {
    label: 'custom',
    value: Privacy.Custom,
    icon: 'ico-gear'
  }
];

function PrivacyControl({
  value = Privacy.Everyone,
  item,
  setValue,
  disabled,
  showLabel = true,
  showDropdown = true,
  feed
}: PrivacyControlProps) {
  const { dialogBackend, i18n } = useGlobal();
  const anchorRef = React.useRef<HTMLButtonElement>();
  const [open, setOpen] = React.useState<boolean>(false);
  const [privacy, setPrivacy] = React.useState(options[Privacy.Everyone]);

  React.useEffect(() => {
    if (Array.isArray(value)) {
      const customValue = options.find(
        option => option.value === Privacy.Custom
      );

      setPrivacy(customValue);

      return;
    }

    const opt =
      options.find(x => x.value === value) || options[Privacy.Everyone];

    setPrivacy(opt);
  }, [setValue, value]);

  const handleClose = () => {
    setOpen(false);
  };

  const handleClick = () => {
    if (!disabled) setOpen(true);
  };

  const handleClickMenu = (item: any) => {
    handleClose();

    if (Privacy.Custom === item.value) {
      selectCustomPrivacy();
    } else {
      setValue(item.value);
    }
  };

  const selectCustomPrivacy = () => {
    dialogBackend
      .present({
        component: 'dialog.DialogCustomListPrivacy',
        props: {
          value: value ?? Privacy.Everyone,
          feed
        }
      })
      .then(value => {
        if (value) {
          setValue(value);
        }
      });
  };

  return (
    <>
      <Tooltip title={item?.tooltip ? item?.tooltip : ''}>
        <Button
          size="smaller"
          color="default"
          ref={anchorRef}
          onClick={handleClick}
          disabled={disabled}
          data-testid={'buttonPrivacy'}
          startIcon={
            showLabel ? (
              <LineIcon icon={item?.icon || privacy?.icon} />
            ) : undefined
          }
          endIcon={
            showDropdown && !disabled && <LineIcon icon="ico-caret-down" />
          }
        >
          {showLabel ? (
            i18n.formatMessage({ id: privacy.label })
          ) : (
            <LineIcon icon={item?.icon || privacy?.icon} />
          )}
        </Button>
      </Tooltip>
      <ClickOutsideListener onClickAway={handleClose} excludeRef={anchorRef}>
        <Popper
          disablePortal
          data-testid="menuPrivacy"
          id={open ? 'privacy-popover' : undefined}
          anchorEl={anchorRef.current}
          open={Boolean(open)}
          popperOptions={{
            strategy: 'fixed'
          }}
          placement="bottom-start"
        >
          <PaperMenu>
            {options
              ? options.map((item, index) => (
                  <MenuItem
                    value={item.value}
                    data-value={item.value}
                    key={index.toString()}
                    onClick={() => handleClickMenu(item)}
                  >
                    <LineIcon
                      icon={item?.icon}
                      aria-label={item.label}
                      role="img"
                      sx={{
                        minWidth: '24px',
                        marginRight: '8px',
                        textAlign: 'center'
                      }}
                    />
                    {i18n.formatMessage({ id: item.label })}
                  </MenuItem>
                ))
              : null}
          </PaperMenu>
        </Popper>
      </ClickOutsideListener>
    </>
  );
}

export default PrivacyControl;

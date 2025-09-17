import { RefOf, useGlobal } from '@metafox/framework';
import { ClickOutsideListener, Popper } from '@metafox/ui';
import { Paper, PopperProps } from '@mui/material';
import React from 'react';
import { styled } from '@mui/material/styles';
import { filterShowWhen } from '@metafox/utils';

const name = 'ProfileAvatarMenu';

const PopperWrapper = styled(Popper, {
  name,
  slot: 'PopperWrapper'
})<{}>(({ theme }) => ({
  zIndex: `${theme.zIndex.snackbar}!important`,
  minWidth: '180px',
  width: 'auto',
  maxHeight: '70vh',
  maxWidth: 300
}));

const PaperMenu = styled(Paper, {
  name,
  slot: 'MuiActionMenu-menu'
})<{}>(({ theme }) => ({
  padding: theme.spacing(1, 0)
}));

interface IProps extends PopperProps {
  anchorRef?: RefOf<HTMLDivElement>;
  appPhotoActive?: boolean;
  appStoryActive?: boolean;
  setOpen?: any;
  item?: any;
  avatarId?: string | number;
}

export default function PopperMenu({
  anchorRef,
  open,
  setOpen,
  appPhotoActive = false,
  appStoryActive = false,
  item,
  avatarId
}: IProps) {
  const { i18n, jsxBackend, navigate } = useGlobal();

  const onClickAvatar = () => {
    navigate({ pathname: `/photo/${avatarId}` }, { state: { asModal: true } });
  };

  const onClickStory = () => {
    navigate({ pathname: `/story/${item.id}` });
  };

  const items = React.useMemo(
    () =>
      filterShowWhen(
        [
          {
            icon: 'ico-story-o',
            label: i18n.formatMessage({ id: 'view_story' }),
            value: 'view_story',
            testid: 'view_story',
            showWhen: ['and', ['truthy', 'appStoryActive']],
            onClick: onClickStory
          },
          {
            icon: 'ico-photo-o',
            label: i18n.formatMessage({ id: 'view_profile_picture' }),
            value: 'view_profile_picture',
            testid: 'view_profile_picture',
            showWhen: [
              'and',
              ['truthy', 'appPhotoActive'],
              ['truthy', 'hasAvatar']
            ],
            onClick: onClickAvatar
          }
        ],
        { appPhotoActive, appStoryActive, hasAvatar: avatarId !== 0 }
      ),
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [appPhotoActive, appStoryActive, onClickAvatar, onClickStory]
  );

  const closeMenu = () => {
    setOpen(false);
  };

  const handleOutsideClick = () => {
    setOpen(false);
  };

  return (
    <ClickOutsideListener
      onClickAway={handleOutsideClick}
      excludeRef={anchorRef}
    >
      <PopperWrapper
        data-testid={'ProfileAvatarMenu'}
        open={Boolean(open)}
        anchorEl={anchorRef.current}
        placement={'bottom'}
      >
        <PaperMenu>
          {items.map((item, index) =>
            jsxBackend.render({
              component: 'menuItem.as.normal',
              props: { key: index, item, closeMenu, variant: 'dropdown' }
            })
          )}
        </PaperMenu>
      </PopperWrapper>
    </ClickOutsideListener>
  );
}

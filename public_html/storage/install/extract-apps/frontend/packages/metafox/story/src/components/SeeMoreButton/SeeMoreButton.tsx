import { ButtonLink, useGlobal } from '@metafox/framework';
import { styled, SxProps } from '@mui/material';
import React from 'react';
import { PauseStatus } from '@metafox/story/types';

const name = 'seeMoreLinkStory';

const SeeMoreLinkStyled = styled(ButtonLink, {
  name,
  slot: 'SeeMoreLink',
  shouldForwardProp: props => props !== 'isPreview'
})<{ isPreview?: boolean }>(({ theme, isPreview }) => ({
  ...(isPreview && {
    position: 'absolute',
    left: '50%',
    bottom: 16,
    transform: 'translate(-50%, 0)'
  }),
  color: '#fff',
  borderColor: '#fff',
  borderRadius: theme.spacing(3),
  zIndex: 3
}));

type Props = {
  link: string;
  sx?: SxProps;
  variant?: string;
  fire?: any;
  isPreview?: boolean;
};

const SeeMoreLink = ({
  link,
  sx,
  variant = 'outlined',
  fire,
  isPreview = true
}: Props) => {
  const { i18n } = useGlobal();

  if (!link) return;

  const onCancel = () => {
    if (!fire) return;

    fire({
      type: 'setForcePause',
      payload: PauseStatus.No
    });
  };

  const onClick = () => {
    if (!fire) return;

    fire({
      type: 'setForcePause',
      payload: PauseStatus.Pause
    });
  };

  return (
    <SeeMoreLinkStyled
      isPreview={isPreview}
      to={link}
      variant={variant}
      sx={sx}
      onClick={onClick}
      onCancel={onCancel}
    >
      {i18n.formatMessage({ id: 'visit_link' })}
    </SeeMoreLinkStyled>
  );
};

export default SeeMoreLink;

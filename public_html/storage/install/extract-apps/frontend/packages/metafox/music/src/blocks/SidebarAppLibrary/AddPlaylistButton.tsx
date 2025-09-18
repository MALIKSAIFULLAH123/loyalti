/**
 * @type: ui
 * name: music.AddPlaylistButton
 */
import { Link, useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { styled } from '@mui/material';
import React from 'react';

const Icon = styled(LineIcon, {
  name: 'Icon'
})(({ theme }) => ({
  color: theme.palette.primary.main,
  fontSize: theme.spacing(2)
}));

export default function EditButton() {
  const { getAcl } = useGlobal();
  const canCreate = getAcl('music.music_playlist.create');

  if (!canCreate) return null;

  return (
    <Link to="/music/playlist/add" underline="none">
      <Icon icon="ico-plus" />
    </Link>
  );
}

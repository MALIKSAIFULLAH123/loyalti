import { LineIcon } from '@metafox/ui';
import { styled, ListItemIcon, ListItem, ListItemText } from '@mui/material';
import { alpha } from '@mui/system/colorManipulator';
import React from 'react';
interface Props {
  item: any;
  presentAlert: (x: any) => void;
}
const name = 'AlertReminderListItem';
const Item = styled(ListItem, {
  name,
  slot: 'Item',
  shouldForwardProp: props => props !== 'isRead'
})<{ isRead?: boolean }>(({ theme, isRead }) => ({
  backgroundColor: isRead
    ? alpha(theme.palette.background.default, 0.5)
    : alpha(theme.palette.primary.light, 0.1),
  '&:not(:last-of-type)': {
    borderBottom: theme.mixins.border('secondary')
  }
}));

export default function AlertReminderList({ item, presentAlert }: Props) {
  const { title, extra } = item;
  const { can_close } = extra || {};
  const [read, setRead] = React.useState(false);

  const handleClick = () => {
    setRead(true);
    presentAlert && presentAlert(item);
  };

  return (
    <Item sx={{ cursor: 'pointer' }} onClick={handleClick} isRead={read}>
      <ListItemIcon>
        <LineIcon icon={can_close ? 'ico-info-circle-o' : 'ico-warning-o'} />
      </ListItemIcon>
      <ListItemText primary={title} />
    </Item>
  );
}

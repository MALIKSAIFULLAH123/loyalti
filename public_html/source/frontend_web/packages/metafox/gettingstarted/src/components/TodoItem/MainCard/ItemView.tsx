import { useGlobal } from '@metafox/framework';
import { TodoItem } from '@metafox/gettingstarted/types';
import { CheckCircleRounded, RadioButtonUnchecked } from '@mui/icons-material';
import {
  ListItem,
  ListItemButton,
  ListItemIcon,
  ListItemText
} from '@mui/material';
import Checkbox from '@mui/material/Checkbox';
import React from 'react';

type Props = {
  item: TodoItem;
  handleActionItem: () => void;
};

export default function TodoItemView({ item, handleActionItem }: Props) {
  const { dialogBackend } = useGlobal();

  if (!item) return null;

  const { ordering, title } = item || {};

  const navigateStep = () => {
    handleActionItem();
    dialogBackend.present({
      component: 'gettingStarted.dialog.steps',
      props: {
        currentStep: ordering
      }
    });
  };

  return (
    <ListItem disablePadding>
      <ListItemButton onClick={navigateStep} dense>
        <ListItemIcon>
          <Checkbox
            sx={{ ml: 0 }}
            edge="start"
            checked={item?.is_done}
            disableRipple
            icon={<RadioButtonUnchecked />}
            checkedIcon={<CheckCircleRounded />}
          />
        </ListItemIcon>
        <ListItemText primary={title} />
      </ListItemButton>
    </ListItem>
  );
}

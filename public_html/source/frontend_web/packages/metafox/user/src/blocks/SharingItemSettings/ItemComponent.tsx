import { SharingPrivacyItem } from '../../types';
import * as React from 'react';
import {
  ListItem,
  ListItemText,
  Box
} from '@mui/material';
import { SelectPopper } from '@metafox/ui';
import useStyles from './styles';
import { camelCase } from 'lodash';

export type Props = {
  item: SharingPrivacyItem;
  onChanged: (value: number, var_name: string) => void;
  classes: any;
  index: number
};

export default function ItemComponent({ item, onChanged, index }: Props) {
  const classes = useStyles();
  const [value, setValue] = React.useState<number>(item.value);

  const handleChange = evt => {
    const newValue = evt?.value;

    setValue(newValue);
    onChanged(parseInt(newValue, 10), item?.var_name);
  };
  
  return (
    <ListItem className={classes.listItem} data-testid={camelCase(`list_sharing_item ${index}`)}>
      <ListItemText primary={item.phrase} sx={{ pr: 2, with: '50%' }} />
      <Box sx={{ width: '50%', textAlign: 'end' }}>
        <SelectPopper name={item.phrase} options={item?.options} value={value} handleChange={handleChange} />
      </Box>
    </ListItem>
  );
}

import React from 'react';
import { Checkbox, FormControlLabel, styled } from '@mui/material';
import { useGlobal } from '@metafox/framework';

const FormControlLabelStyled = styled(FormControlLabel)(({ theme }) => ({
  width: '100%',
  margin: '0 !important',
  padding: theme.spacing(0.5, 1, 0.5, 0),
  position: 'relative'
}));

function CollectionItem({ item }) {
  const { useBatchSelectContext } = useGlobal();

  const valueContext = useBatchSelectContext();

  const { handleToggleItem, checkedList, loading } = valueContext;

  const checkedProp = checkedList.some(x => x === item.id);

  const [checked, setChecked] = React.useState(checkedProp);

  const handleCheckboxChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    handleToggleItem(item?.id, !checked);
    setChecked(prev => !prev);
  };

  React.useEffect(() => {
    setChecked(checkedProp);
  }, [checkedProp]);

  if (!item) return null;

  return (
    <FormControlLabelStyled
      control={
        <Checkbox
          disabled={loading}
          checked={checked}
          onChange={handleCheckboxChange}
          color="primary"
          size="small"
        />
      }
      label={item.name}
    />
  );
}

export default CollectionItem;

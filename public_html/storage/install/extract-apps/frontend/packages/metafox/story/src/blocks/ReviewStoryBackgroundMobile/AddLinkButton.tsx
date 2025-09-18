import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { styled, IconButton } from '@mui/material';
import React from 'react';

const name = 'storyButtonAddLinkExpand';

const IconButtonStyled = styled(IconButton, { name, slot: 'root' })(
  ({ theme }) => ({
    width: '40px',
    height: '40px',
    color: theme.palette.text.primary,
    backgroundColor: theme.palette.background.default,
    '&:hover': {
      backgroundColor: theme.palette.background.default
    },
    '& span': {
      fontSize: theme.mixins.pxToRem(16),
      fontWeight: theme.typography.fontWeightSemiBold
    },
    marginBottom: theme.spacing(2)
  })
);

type AddLinkProps = {
  updateItem: (data: any) => void;
  item?: any;
  nameField?: string;
};

const AddLinkButton = (props: AddLinkProps) => {
  const { dialogBackend } = useGlobal();

  const addExpendLink = () => {
    dialogBackend.present({
      component: 'story.dialog.addExpandLink',
      props
    });
  };

  return (
    <IconButtonStyled onClick={addExpendLink}>
      <LineIcon icon="ico-link" />
    </IconButtonStyled>
  );
};

export default AddLinkButton;

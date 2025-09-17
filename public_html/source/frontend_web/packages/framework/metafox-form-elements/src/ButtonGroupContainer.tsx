/**
 * @type: formElement
 * name: form.element.ButtonGroup
 * chunkName: formBasic
 */
import Button from '@mui/material/Button';
import ButtonGroup from '@mui/material/ButtonGroup';
import React from 'react';

/**
 * @todo: TBD
 */
const ButtonGroupContainer = () => (
  <ButtonGroup color="primary" aria-label="outlined primary button group">
    <Button>One</Button>
    <Button>Two</Button>
    <Button>Three</Button>
  </ButtonGroup>
);

export default ButtonGroupContainer;

/**
 * @type: formElement
 * name: form.element.CollapseContainer
 */
import { Element } from '@metafox/form';
import { useGlobal } from '@metafox/framework';
import { Box, styled, Typography } from '@mui/material';
import { map } from 'lodash';
import React from 'react';

const Header = styled(Box, {
  name: 'MuiFormContainer',
  slot: 'ToggleHeader'
})(({ theme }) => ({
  position: 'relative',
  display: 'flex',
  flexDirection: 'row',
  flexItems: 'center',
  paddingTop: theme.spacing(2),
  cursor: 'pointer'
}));

const Space = styled('div', {
  name: 'MuiFormContainer',
  slot: 'Space'
})({ position: 'relative', flex: 1 });

const Divider = styled('div', {
  name: 'MuiFormContainer',
  slot: 'Divider'
})(({ theme }) => ({
  borderTop: '1px solid',
  borderColor: theme.palette.border?.secondary,
  position: 'absolute',
  left: 0,
  right: 0,
  top: '50%',
  overflow: 'hidden'
}));

const Root = styled(Box, {
  name: 'MuiFormContainer',
  slot: 'Root'
})(({ theme }) => ({
  position: 'relative'
}));

const Body = styled(Box, {
  name: 'MuiFormContainer',
  slot: 'Body'
})<{ horizontal?: boolean; closed?: boolean }>(
  ({ theme, closed, horizontal }) => ({
    position: 'relative',
    padding: theme.spacing(1, 0),
    ...(horizontal
      ? {
          display: 'flex',
          alignItems: 'center',
          flexWrap: 'wrap',
          '&>div': {
            paddingRight: theme.spacing(1)
          }
        }
      : {
          display: 'flex',
          flexDirection: 'column'
        }),
    ...(closed && { display: 'none' })
  })
);

export default function CollapseContainer({ config }) {
  const {
    label,
    description,
    elements,
    variant,
    open,
    group = 'CollapseContainer'
  } = config;
  const { useToggleGroup } = useGlobal();
  const [opened, toggle] = useToggleGroup(group, label, open);

  return (
    <Root>
      <Header onClick={toggle}>
        <Typography
          variant="h5"
          component="h6"
          sx={{ pr: 2, bgColor: 'paper' }}
        >
          {label ?? 'Unlabeled'}
        </Typography>
        <Space>
          <Divider />
        </Space>
      </Header>
      {opened && description ? (
        <Typography sx={{ py: 1 }} color="text.secondary" variant="body2">
          {description}
        </Typography>
      ) : null}
      <Body horizontal={variant === 'horizontal'} closed={!opened}>
        {map(elements, (config, key) => (
          <Element key={key.toString()} config={config} />
        ))}
      </Body>
    </Root>
  );
}

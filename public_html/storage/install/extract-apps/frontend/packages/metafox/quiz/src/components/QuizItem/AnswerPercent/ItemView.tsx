import { ItemView, ItemTitle } from '@metafox/ui';
import { Box, LinearProgress, styled, Typography } from '@mui/material';
import React from 'react';

const ProgressItem = styled(Box)(({ theme }) => ({
  height: theme.spacing(2.5),
  display: 'flex',
  alignItems: 'center'
}));

const LinearProgressStyled = styled(LinearProgress)(({ theme }) => ({
  flex: 1,
  minWidth: 0,
  margin: 0,
  marginRight: theme.spacing(1),
  height: `${theme.spacing(1)} !important`,
  borderRadius: theme.shape.borderRadius / 2,
  backgroundColor: theme.palette.action.selected,
  '& > div': {
    borderRadius: theme.shape.borderRadius / 2
  }
}));

const ProgressPercent = styled(Typography)(({ theme }) => ({
  minWidth: 40,
  color: theme.palette.text.secondary,
  fontSize: theme.mixins.pxToRem(13)
}));

export default function AnswerPercent({
  item,
  identity,
  wrapAs,
  wrapProps
}: any) {
  if (!item) return null;

  const { answers = [] } = item;

  return (
    <>
      {answers.map(item => (
        <ItemView
          key={item.id}
          wrapAs={wrapAs}
          wrapProps={wrapProps}
          testid={`${item.resource_name}`}
          data-eid={identity}
        >
          <ItemTitle>{item.answer}</ItemTitle>
          <ProgressItem>
            <LinearProgressStyled
              variant="determinate"
              value={item.percent_value || 0}
            />
            <ProgressPercent component="span">{item.percent}</ProgressPercent>
          </ProgressItem>
        </ItemView>
      ))}
    </>
  );
}

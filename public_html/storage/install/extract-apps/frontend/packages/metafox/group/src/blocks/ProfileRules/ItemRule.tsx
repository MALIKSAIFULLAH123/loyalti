import { GroupRuleProps } from '@metafox/group/components/GroupManager/MembershipQuestion/types';
import { Box, styled, Typography } from '@mui/material';
import React from 'react';
import HtmlViewer from '@metafox/html-viewer';

const Root = styled(Box)(({ theme }) => ({
  display: 'flex',
  flexDirection: 'column'
}));

const TitleStyled = styled(Box)(({ theme }) => ({
  display: 'flex',
  flexDirection: 'row',
  padding: theme.spacing(1, 0)
}));

const NumberIndex = styled(Typography)(({ theme }) => ({
  minWidth: theme.spacing(3)
}));

const ContentStyled = styled(Box)(({ theme }) => ({
  marginLeft: theme.spacing(3)
}));

export default function ItemRule({
  index,
  title,
  description,
  ...rest
}: GroupRuleProps & { index: number }) {
  return (
    <Root>
      <TitleStyled>
        <NumberIndex component="h3" variant="h5" color="text.primary">
          {index + 1}
        </NumberIndex>
        <Typography component="h3" color="text.primary" variant="h5">
          {title}
        </Typography>
      </TitleStyled>
      <ContentStyled>
        <Typography paragraph variant="body1" color="text.secondary">
          <HtmlViewer html={description || ''} />
        </Typography>
      </ContentStyled>
    </Root>
  );
}

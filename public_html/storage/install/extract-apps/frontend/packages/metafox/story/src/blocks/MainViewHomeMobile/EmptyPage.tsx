import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { styled, Box } from '@mui/material';
import * as React from 'react';

const name = 'EmptyDataStory';

const RootStyled = styled(Box, { name, slot: 'root' })(({ theme }) => ({
  display: 'flex',
  flexDirection: 'column',
  justifyContent: 'center',
  padding: theme.spacing(0, 2),
  alignItems: 'center'
}));

const LineIconStyled = styled(LineIcon, { name, slot: 'LineIconStyled' })(
  ({ theme }) => ({
    fontSize: theme.mixins.pxToRem(72),
    color: '#cecece',
    marginBottom: theme.spacing(4)
  })
);

const TitleStyled = styled(Box, { name, slot: 'TitleStyled' })(({ theme }) => ({
  color: '#fff',
  fontSize: theme.mixins.pxToRem(24),
  fontWeight: theme.typography.fontWeightBold,
  marginBottom: theme.spacing(1.5),
  textAlign: 'center',
  [theme.breakpoints.down('xs')]: {
    fontSize: theme.mixins.pxToRem(18)
  }
}));

const DescriptionStyled = styled(Box, { name, slot: 'DescriptionStyled' })(
  ({ theme }) => ({
    fontSize: theme.mixins.pxToRem(18),
    color: theme.palette.text.secondary,
    textAlign: 'center',
    [theme.breakpoints.down('xs')]: {
      fontSize: theme.mixins.pxToRem(15)
    }
  })
);

interface Props {
  icon: string;
  title: string;
  description?: string;
}

export default function EmptyPage({
  icon,
  title = 'no_content',
  description
}: Props) {
  const { i18n } = useGlobal();

  return (
    <RootStyled>
      <LineIconStyled icon={icon} />
      <TitleStyled>
        <span>{i18n.formatMessage({ id: title })}</span>
      </TitleStyled>
      {description ? (
        <DescriptionStyled>
          {i18n.formatMessage({ id: description })}
        </DescriptionStyled>
      ) : null}
    </RootStyled>
  );
}

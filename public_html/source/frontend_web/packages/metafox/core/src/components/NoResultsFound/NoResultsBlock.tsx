/**
 * @type: ui
 * name: core.block.no_results
 * title: No Results
 * keywords: no content
 */
import { useGlobal } from '@metafox/framework';
import { styled, Typography } from '@mui/material';
import * as React from 'react';

interface Props {
  title?: string;
  image?: string;
  color?:
    | 'inherit'
    | 'initial'
    | 'primary'
    | 'secondary'
    | 'textPrimary'
    | 'textSecondary'
    | 'error';
}

const Root = styled('div', { name: 'Root' })(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  flexDirection: 'column',
  padding: theme.spacing(5, 2)
}));

const Title = styled(Typography, { name: 'Title' })(({ theme }) => ({
  fontWeight: 'bold',
  textAlign: 'center',
  fontSize: theme.mixins.pxToRem(40),
  marginBottom: theme.spacing(6),
  [theme.breakpoints.down('xs')]: {
    fontSize: theme.mixins.pxToRem(24)
  }
}));

const Image = styled('img', { name: 'Image' })(() => ({
  maxWidth: '100%'
}));

export default function NoResultsFound({
  title = 'no_results_found',
  color = 'primary'
}: Props) {
  const { assetUrl, i18n } = useGlobal();

  const image = assetUrl('layout.image_no_results');

  return (
    <Root data-testid="noResultFound">
      <Title
        color={color}
        variant="h3"
        children={i18n.formatMessage({ id: title })}
      />
      <Image src={image} alt={title} />
    </Root>
  );
}

/**
 * @type: block
 * name: core.block.error403
 * title: Permission Denied Error
 * keywords: general
 * experiment: true
 */
import { useGlobal } from '@metafox/framework';
import { Box, Typography } from '@mui/material';
import { styled } from '@mui/material/styles';
import * as React from 'react';

type ErrorBlockProps = {
  title?: string;
  image?: string;
  content?: string;
  color?:
    | 'inherit'
    | 'initial'
    | 'primary'
    | 'secondary'
    | 'textPrimary'
    | 'textSecondary'
    | 'error';
};

const Error403Root = styled(Box, {
  name: 'Error403',
  slot: 'Root'
})(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  flexDirection: 'column',
  padding: theme.spacing(9, 2),
  [theme.breakpoints.down('xs')]: {
    padding: theme.spacing(5, 2)
  },
  '& + .error403Block': {
    display: 'none'
  }
}));

const Error403Image = styled('img', {
  name: 'Error403',
  slot: 'Image'
})(({ theme }) => ({
  maxWidth: '100%'
}));

const Error403Title = styled(Typography, {
  name: 'Error403',
  slot: 'Title'
})<{ component: string; variant: string }>(({ theme }) => ({
  maxWidth: '100%',
  marginBottom: theme.spacing(3.5),
  fontWeight: 'bold',
  textAlign: 'center',
  color: theme.palette.text.primary,
  [theme.breakpoints.down('sm')]: {
    fontSize: theme.mixins.pxToRem(40)
  },
  [theme.breakpoints.down('xs')]: {
    fontSize: theme.mixins.pxToRem(20)
  }
}));

const Error403Content = styled(Typography, {
  name: 'Error403',
  slot: 'Content'
})<{ component: string; variant: string }>(({ theme }) => ({
  marginBottom: theme.spacing(5),
  color: theme.palette.text.hint,
  fontSize: theme.mixins.pxToRem(18),
  textAlign: 'center',
  [theme.breakpoints.down('xs')]: {
    fontSize: theme.mixins.pxToRem(13)
  }
}));

const Error403Block = ({
  title = 'content_is_not_available',
  content = 'content_not_available_content'
}: ErrorBlockProps) => {
  const { usePageParams, i18n, assetUrl } = useGlobal();
  const pageParams = usePageParams();
  const image = assetUrl('layout.image_error_403');
  const contentData = pageParams?.errorDescription || content;

  return (
    <Error403Root className="error403Block" data-testid="error403">
      <Error403Title component="h2" variant="h1">
        {pageParams?.title || i18n.formatMessage({ id: title })}
      </Error403Title>
      {contentData ? (
        <Error403Content component="div" variant="subtitle2">
          {i18n.formatMessage({ id: contentData })}
        </Error403Content>
      ) : null}
      <Error403Image src={image} alt={title} />
    </Error403Root>
  );
};
export default Error403Block;

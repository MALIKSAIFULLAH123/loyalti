/**
 * @type: block
 * name: core.block.error404
 * title: Error 404 - Page Not Found
 * keywords: general
 * description:
 * thumbnail:
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

const Error404Root = styled(Box, {
  name: 'Error404',
  slot: 'Root'
})(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  flexDirection: 'column',
  padding: theme.spacing(5, 2),
  '& + .error404Block': {
    display: 'none'
  }
}));

const Error404Image = styled('img', {
  name: 'Error404',
  slot: 'Image'
})(({ theme }) => ({
  maxWidth: '100%'
}));

const Error404Title = styled(Typography, {
  name: 'Error404',
  slot: 'Title'
})<{ component: string; variant: string }>(({ theme }) => ({
  maxWidth: '100%',
  marginBottom: theme.spacing(2),
  fontWeight: 'bold',
  textAlign: 'center',
  [theme.breakpoints.down('sm')]: {
    fontSize: theme.mixins.pxToRem(40)
  },
  [theme.breakpoints.down('xs')]: {
    fontSize: theme.mixins.pxToRem(20)
  }
}));

const Error404Content = styled(Typography, {
  name: 'Error404',
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

const Error404Block = ({
  title = '404 - Page Not Found',
  color = 'primary',
  content
}: ErrorBlockProps) => {
  const { usePageParams, assetUrl, i18n } = useGlobal();
  const pageParams = usePageParams();

  const image = assetUrl('layout.image_error_404');
  const debugTraceId = pageParams?.debugTraceId;
  const contentData = pageParams?.errorDescription || content;

  return (
    <Error404Root className="error404Block" data-testid="error404">
      <Error404Title color={color} component="h2" variant="h1">
        {pageParams?.title || title}
      </Error404Title>
      {contentData ? (
        <Error404Content component="div" variant="subtitle2">
          {i18n.formatMessage({ id: contentData })}
        </Error404Content>
      ) : null}
      {debugTraceId ? (
        <Typography color="primary" variant="body1">
          Trace-ID: {debugTraceId}
        </Typography>
      ) : null}
      <Error404Image src={image} alt={title} />
    </Error404Root>
  );
};
export default Error404Block;

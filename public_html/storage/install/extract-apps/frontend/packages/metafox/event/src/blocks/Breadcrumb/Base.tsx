import { Link, useGlobal } from '@metafox/framework';
import { Block, BlockContent } from '@metafox/layout';
import { styled } from '@mui/material';
import React from 'react';

const LinkStyled = styled(Link, { name: 'breadcrumbLink' })(
  ({ theme }) => ({
    fontSize: theme.mixins.pxToRem(18), 
    '&:hover': {
      textDecoration: 'none'
    }
  })
);

export default function BreadcrumbEvent({ to, title }) {
  const { compactUrl, usePageParams, i18n } = useGlobal();
  const pageParams = usePageParams();

  return (
    <Block>
      <BlockContent>
        <LinkStyled to={compactUrl(to, pageParams)} color="primary">
          {i18n.formatMessage({ id: title })}
        </LinkStyled>
      </BlockContent>
    </Block>
  );
}

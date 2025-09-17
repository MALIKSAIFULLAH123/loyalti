import { useGlobal, RouteLink as Link } from '@metafox/framework';
import { Block, BlockContent } from '@metafox/layout';
import { Box } from '@mui/material';
import * as React from 'react';
import { LineIcon } from '@metafox/ui';

export type Props = {
  title?: string;
};

export default function BreadcrumbBlock({ title }: Props) {
  const { usePageMeta } = useGlobal();

  const data = usePageMeta();
  const { breadcrumbs = [] } = data || {};

  if (!breadcrumbs?.length) return null;

  return (
    <Block testid="blockBreadcrumb">
      <BlockContent>
        {breadcrumbs.map((item, index) => (
          <Box
            component={'span'}
            sx={{
              display: 'inline-flex',
              alignItems: 'center',
              '&:last-child': { fontWeight: 700 }
            }}
            key={item?.label.toString()}
          >
            <Link to={item?.to}>{item?.label}</Link>
            {breadcrumbs?.length - 1 !== index ? (
              <Box mx={1} component={'span'} color={'text.hint'}>
                <LineIcon sx={{ fontSize: '10px' }} icon="ico-angle-right" />
              </Box>
            ) : null}
          </Box>
        ))}
      </BlockContent>
    </Block>
  );
}

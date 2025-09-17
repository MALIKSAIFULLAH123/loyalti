import { useGlobal } from '@metafox/framework';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import { UIBlockViewProps } from '@metafox/ui';
import React from 'react';
import data from './data.json';
import ItemView from './ItemView';
import { Box, styled } from '@mui/material';

export interface Props extends UIBlockViewProps {}

const name = 'AdminCpSiteStatistics';

const Root = styled(Box, { name, slot: 'Root' })(({ theme }) => ({
  display: 'flex',
  flexWrap: 'wrap'
}));

export default function AdminSiteStatistics({ blockProps, title }: Props) {
  const [open, setOpen] = React.useState<boolean>(false);
  const { i18n } = useGlobal();

  const handleToggle = () => setOpen(open => !open);
  const items = open ? data : data.filter((_, index) => index < 4);

  return (
    <Block>
      <BlockHeader title={title} />
      <BlockContent>
        <Root>
          {items.map((item, index) => (
            <ItemView
              {...item}
              index={index % 4}
              key={index.toString()}
            />
          ))}
          {open ? (
            <ItemView
              index={0}
              label={i18n.formatMessage({ id: 'less' })}
              icon="ico-angle-double-up"
              onClick={handleToggle}
            />
          ) : (
            <ItemView
              index={0}
              classes={classes}
              label={i18n.formatMessage({ id: 'more' })}
              icon="ico-angle-double-down"
              style={{ cursor: 'pointer' }}
              onClick={handleToggle}
            />
          )}
        </Root>
      </BlockContent>
    </Block>
  );
}

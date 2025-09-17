import { BlockViewProps, Link, useAppMenu } from '@metafox/framework';
import { Block, BlockContent } from '@metafox/layout';
import React from 'react';

export interface Props extends BlockViewProps {}

export default function SidebarFooter({ blockProps }: Props) {
  const menu = useAppMenu('core', 'footerMenu');

  if (!menu?.items) return null;

  const { items } = menu;
  const license = 'Phpfox © 2017';

  return (
    <Block>
      <BlockContent>
        {items.map(item => (
          <Link
            key={item.to}
            role="link"
            variant="caption"
            style={{ paddingRight: 8 }}
            color="textSecondary"
            {...item}
          >
            {item.label}
          </Link>
        ))}
        <Link
          role="link"
          to="#"
          variant="caption"
          style={{ paddingRight: 8 }}
          color="textSecondary"
        >
          {license}
        </Link>
      </BlockContent>
    </Block>
  );
}

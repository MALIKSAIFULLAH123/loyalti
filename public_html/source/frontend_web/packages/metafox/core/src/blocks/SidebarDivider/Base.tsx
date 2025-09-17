import { BlockViewProps, useGlobal } from '@metafox/framework';
import { Block, BlockContent } from '@metafox/layout';
import { Divider } from '@mui/material';
import * as React from 'react';

export interface Props extends BlockViewProps {
  dividerVariant: 'middle' | 'inset';
}

export default function SidebarDivider({ dividerVariant, blockProps }: Props) {
  const { useTheme } = useGlobal();
  const theme = useTheme();

  return (
    <Block>
      <BlockContent>
        <Divider
          variant={dividerVariant ?? 'inset'}
          sx={{
            borderColor: theme.palette.divider
          }}
        />
      </BlockContent>
    </Block>
  );
}

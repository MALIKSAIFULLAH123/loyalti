import { EventItemProps } from '@metafox/event';
import { BlockViewProps, useGlobal } from '@metafox/framework';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import { styled } from '@mui/material';
import React from 'react';

export type Props = BlockViewProps & EventItemProps;

const ContentWrapperStyled = styled('div', { name: 'ContentWrapperStyled' })(
  ({ theme }) => ({
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    '& .borderRight': {
      borderRight: `1px solid ${theme.palette.border?.secondary}`
    }
  })
);

const ContentStyled = styled('div', {
  name: 'ContentStyled',
  shouldForwardProp: props => props !== 'disablePointer'
})<{ disablePointer?: boolean }>(({ theme, disablePointer }) => ({
  textAlign: 'center',
  flex: 1,
  fontSize: theme.mixins.pxToRem(15),
  cursor: disablePointer ? 'unset' : 'pointer',
  padding: theme.spacing(1, 0),
  '&:hover': {
    background: !disablePointer && theme.palette.action.hover,
    '&.borderRight': {
      borderTopLeftRadius: theme.shape.borderRadius,
      borderBottomLeftRadius: theme.shape.borderRadius
    },
    '&:not(.borderRight)': {
      borderTopRightRadius: theme.shape.borderRadius,
      borderBottomRightRadius: theme.shape.borderRadius
    }
  }
}));

const HighlyStyled = styled('div', {
  name: 'HighlyStyled'
})(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(24),
  fontWeight: theme.typography.fontWeightBold
}));

const GuestViewEvent = ({ title, item, ...rest }: Props) => {
  const { i18n, dispatch } = useGlobal();

  if (!item) return null;

  const can_view_members = item.extra?.can_view_members;

  const { statistic } = Object.assign({}, item);

  const handleClick = (defaultTab: string) => {
    if (!can_view_members) return;

    dispatch({
      type: 'event/viewAllGuest',
      payload: { identity: item._identity, defaultTab }
    });
  };

  return (
    <Block {...rest}>
      <BlockHeader title={title} />
      <BlockContent>
        <ContentWrapperStyled>
          <ContentStyled
            disablePointer={!can_view_members}
            className="borderRight"
            onClick={() => handleClick('joined')}
          >
            <HighlyStyled>{statistic?.total_member}</HighlyStyled>
            <span>
              {i18n.formatMessage({ id: item.is_ended ? 'went' : 'going' })}
            </span>
          </ContentStyled>
          <ContentStyled
            disablePointer={!can_view_members}
            onClick={() => handleClick('interested')}
          >
            <HighlyStyled>{statistic?.total_interested}</HighlyStyled>
            <span>{i18n.formatMessage({ id: 'interested' })}</span>
          </ContentStyled>
        </ContentWrapperStyled>
      </BlockContent>
    </Block>
  );
};

export default GuestViewEvent;

/* eslint-disable react/jsx-no-undef */
/* eslint-disable react/jsx-key */
import { useGlobal } from '@metafox/framework';
import { Block, BlockContent, BlockHeader, BlockTitle } from '@metafox/layout';
import {
  ItemMedia,
  ItemText,
  ItemTitle,
  UIBlockViewProps,
  UserAvatar,
  UserName
} from '@metafox/ui';
import { Skeleton, styled } from '@mui/material';
import React from 'react';

export interface Props extends UIBlockViewProps {}

const ItemContent = styled('div', { slot: 'ItemContent' })(({ theme }) => ({
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',
  paddingTop: theme.spacing(2),
  marginTop: theme.spacing(2),
  borderTop: '1px solid #eaeaea',
  '&:first-of-type': {
    paddingTop: 0,
    marginTop: 0,
    border: 'none'
  }
}));

const ItemMediaWrapper = styled(ItemMedia, { slot: 'ItemMedia' })(
  ({ theme }) => ({
    paddingRight: theme.spacing(1.5)
  })
);

const Title = styled(ItemTitle, { slot: 'ItemTitle' })(({ theme }) => ({
  '& p': {
    fontWeight: 'bold',
    color: theme.palette.grey['A700']
  }
}));

const Ip = styled('div', { slot: 'Ip' })(({ theme }) => ({
  color: theme.palette.grey[700],
  marginTop: theme.spacing(0.5),
  fontSize: 13
}));

const BlockContentSkeleton = styled(BlockContent, {
  slot: 'BlockContentSkeleton'
})(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  '& .ItemView-media': {
    paddingRight: theme.spacing(2)
  }
}));

export default function AdminItemStats({ title }: Props) {
  const { useFetchDetail, i18n } = useGlobal();

  const [data, loading] = useFetchDetail({
    dataSource: {
      apiUrl: 'admincp/dashboard/admin-active'
    }
  });

  if (loading) {
    return (
      <Block>
        <BlockHeader>
          {title ? (
            <BlockTitle> {i18n.formatMessage({ id: title })} </BlockTitle>
          ) : null}
        </BlockHeader>
        <BlockContentSkeleton>
          <ItemMedia>
            <Skeleton variant="circular" width={48} height={48} />
          </ItemMedia>
          <ItemText>
            <Skeleton width={200} />
            <Skeleton width={160} />
          </ItemText>
        </BlockContentSkeleton>
      </Block>
    );
  }

  return (
    <Block>
      <BlockHeader>
        {title ? (
          <BlockTitle>{i18n.formatMessage({ id: title })}</BlockTitle>
        ) : null}
      </BlockHeader>
      {!loading && data ? (
        <BlockContent>
          {data.map((item, index) => (
            <ItemContent key={index}>
              <ItemMediaWrapper>
                <UserAvatar
                  hoverCard={false}
                  user={item.user}
                  size={48}
                  target="_blank"
                />
              </ItemMediaWrapper>
              <ItemText>
                <Title>
                  <UserName
                    color="inherit"
                    hoverCard={false}
                    user={item?.user}
                    target="_blank"
                  />
                </Title>
                <Ip>{item.ip_address}</Ip>
              </ItemText>
            </ItemContent>
          ))}
        </BlockContent>
      ) : null}
    </Block>
  );
}

import { useGlobal } from '@metafox/framework';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import { FETCH_MFA_SETTING } from '@metafox/user/actions/accountSettings';
import { Box, Skeleton, styled } from '@mui/material';
import * as React from 'react';
import ItemFactor from './ItemFactor';

export type Props = {
  loaded: boolean;
  data: Record<string, any>[];
  title: string;
};

const WrapperContentStyled = styled(Box)(({ theme }) => ({
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',
  width: '100%',
  marginBottom: theme.spacing(0.5),
  marginTop: theme.spacing(0.5)
}));

const ContentInfoStyled = styled(Box)(({ theme }) => ({
  display: 'flex',
  flexDirection: 'row',
  width: '80%'
}));

const LineInfoStyled = styled(Box)(({ theme }) => ({
  display: 'flex',
  justifyContent: 'center',
  flexDirection: 'column',
  width: '100%'
}));

const ContentStyled = styled(Box)(({ theme }) => ({
  '& .item': {
    padding: theme.spacing(2.75, 0),
    borderBottom: theme.mixins.border('secondary'),
    '&:first-of-type': {
      paddingTop: 6
    },
    '&:last-child': {
      paddingBottom: 6,
      borderBottom: 'none'
    }
  }
}));

export default function GeneralSettings({ title, data, loaded }: Props) {
  const { dispatch } = useGlobal();

  // refresh for new data anytime its mount
  React.useEffect(() => {
    dispatch({ type: FETCH_MFA_SETTING });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  if (!loaded) {
    return (
      <Block>
        <BlockHeader title={title} />
        <BlockContent>
          <Box>
            <Skeleton variant="text" width={150} />
            <WrapperContentStyled>
              <ContentInfoStyled>
                <Skeleton
                  variant="rectangular"
                  height={90}
                  width={125}
                  sx={{ mr: 1 }}
                />
                <LineInfoStyled>
                  <Skeleton variant="text" width={'100%'} />
                  <Skeleton variant="text" width={'100%'} />
                </LineInfoStyled>
              </ContentInfoStyled>
              <Skeleton variant="text" height={50} width={60} />
            </WrapperContentStyled>
          </Box>
        </BlockContent>
      </Block>
    );
  }

  return (
    <Block>
      <BlockHeader title={title} />
      {data?.length ? (
        <BlockContent>
          <ContentStyled>
            {data.map(item => (
              <ItemFactor key={`p${item?.service}`} data={item} />
            ))}
          </ContentStyled>
        </BlockContent>
      ) : null}
    </Block>
  );
}

import { BlockViewProps, ButtonLink, useGlobal } from '@metafox/framework';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import { LineIcon, Image } from '@metafox/ui';
import { Box, Typography, styled, Stack, useTheme } from '@mui/material';
import React from 'react';

export type Props = BlockViewProps;

const BlockContentWrapperStyled = styled(Box, { 
  name: 'ActivityPoint',
  slot: 'activityContentGetStarted',
  overridesResolver(props, styles) {
    return [styles.activityContentGetStarted];
  }
 })(
  ({ theme }) => ({
    display: 'flex',
    paddingTop: theme.spacing(3),
    paddingBottom: theme.spacing(3),
    [theme.breakpoints.down('sm')]: {
      flexDirection: 'column'
    }
  })
);

const MainContentStyled = styled(Box, { 
  name: 'ActivityPoint',
  slot: 'activityMainContent',
  overridesResolver(props, styles) {
    return [styles.activityMainContent];
  }
 })(
  ({ theme }) => ({
    flex: 1,
    paddingLeft: theme.spacing(2),
    [theme.breakpoints.down('sm')]: {
      paddingRight: theme.spacing(2),
      paddingBottom: theme.spacing(4)
    }
  })
);

const ActivityImage = styled(Image, { name: 'ActivityImage' })(({ theme }) => ({
  paddingLeft: '10%',
  paddingRight: '10%',
  '& img': {
    border: 'none',
    background: 'transparent'
  }
}));

const BoxImage = styled(Box, {
  name: 'ActivityPoint',
  slot: 'activityBoxImage',
  overridesResolver(props, styles) {
    return [styles.activityBoxImage];
  }
})(({ theme }) => ({
  flex: 1,
  alignSelf: 'center',
  [theme.breakpoints.up('sm')]: {
    maxWidth: '40%'
  }
}));

export default function Base({ title, ...rest }: Props) {
  const { i18n, assetUrl } = useGlobal();
  const theme = useTheme();
  const activityImage =
    theme.palette.mode === 'dark'
      ? assetUrl('activitypoint.get_started_dark')
      : assetUrl('activitypoint.get_started');

  return (
    <Block testid="activityGetStarted" {...rest}>
      <BlockHeader title={title} />
      <BlockContent>
        <BlockContentWrapperStyled>
          <MainContentStyled>
            <Typography
              variant="h2"
              sx={{ fontWeight: 'fontWeightRegular', pb: 3 }}
            >
              {i18n.formatMessage({ id: 'getting_started' })}
            </Typography>
            <Typography variant="body1" color="text.secondary">
              {i18n.formatMessage({ id: 'getting_started_description' })}
            </Typography>
            <Stack
              spacing={2}
              sx={{ mt: 2 }}
              direction={{ xs: 'column', sm: 'row' }}
            >
              <ButtonLink
                variant="outlined"
                startIcon={<LineIcon icon="ico-folders-o" />}
                to={'/activitypoint/packages'}
              >
                <Typography variant="h5">
                  {i18n.formatMessage({ id: 'view_all_packages' })}
                </Typography>
              </ButtonLink>
              <ButtonLink
                variant="outlined"
                startIcon={<LineIcon icon="ico-crown-o" />}
                to={'/activitypoint/how-to-earn'}
              >
                <Typography variant="h5">
                  {i18n.formatMessage({ id: 'earn_more_points' })}
                </Typography>
              </ButtonLink>
            </Stack>
          </MainContentStyled>
          <BoxImage>
            <ActivityImage src={activityImage} />
          </BoxImage>
        </BlockContentWrapperStyled>
      </BlockContent>
    </Block>
  );
}

Base.displayName = 'ActivityPoint_GetStarted';

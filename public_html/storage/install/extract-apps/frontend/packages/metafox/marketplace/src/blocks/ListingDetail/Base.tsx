import {
  RouteLink as Link,
  useGlobal,
  CORE_GOOGLE_GOOGLE_MAP_API_KEY
} from '@metafox/framework';
import HtmlViewer from '@metafox/html-viewer';
import { Block, BlockContent } from '@metafox/layout';
import {
  FeaturedFlag,
  LineIcon,
  PendingFlag,
  SponsorFlag,
  AttachmentItem,
  Tags,
  DotSeparator,
  ItemAction,
  HtmlViewerWrapper,
  CategoryList,
  TruncateText,
  AuthorInfo
} from '@metafox/ui';
import { Box, Button, styled, Typography, IconButton } from '@mui/material';
import * as React from 'react';
import { MarketplaceDetailViewProps } from '../../types';
import ListingImages from './ListingImages';
import {
  BOUNDS_EAST,
  BOUNDS_NORTH,
  BOUNDS_SOUTH,
  BOUNDS_WEST
} from '@metafox/marketplace';

const name = 'MarketplaceDetail';

const ItemWrapper = styled('div', {
  name,
  slot: 'root',
  shouldForwardProp: prop => prop !== 'isModalView'
})<{ isModalView: boolean }>(({ theme, isModalView }) => ({
  position: 'relative',
  borderRadius: theme.shape.borderRadius,
  margin: 'auto',
  [theme.breakpoints.down('xs')]: {
    padding: 0
  },
  ...(isModalView && {
    padding: theme.spacing(2, 0)
  })
}));
const ListingHeader = styled('div', {
  name,
  slot: 'listingHeader',
  overridesResolver(props, styles) {
    return [styles.listingHeader];
  }
})(({ theme }) => ({
  display: 'flex',
  [theme.breakpoints.down('sm')]: {
    display: 'block',
    flexDirection: 'column'
  }
}));
const Images = styled('div', {
  name,
  slot: 'images',
  overridesResolver(props, styles) {
    return [styles.galleyImages];
  }
})(({ theme }) => ({
  float: 'left',
  marginRight: theme.spacing(2),
  marginBottom: theme.spacing(2),
  width: '40%',
  minWidth: '400px',
  [theme.breakpoints.down('sm')]: {
    width: '100%',
    minWidth: 'auto',
    float: 'none'
  }
}));

const Container = styled('div', { name, slot: 'container' })(({ theme }) => ({
  flexGrow: 1
}));
const InnerContainer = styled('div', { name, slot: 'InnerContainer' })(
  ({ theme }) => ({
    position: 'relative'
  })
);

const FlagWrapper = styled('div', { name, slot: 'FlagWrapper' })(
  ({ theme }) => ({
    display: 'inline-flex',
    margin: theme.spacing(0, 0, 0, -0.5),
    float: 'left'
  })
);
const Price = styled('div', { name, slot: 'price' })(({ theme }) => ({
  color: theme.palette.error.main,
  fontSize: '1.125rem',
  fontWeight: theme.typography.fontWeightBold
}));
const ItemOuter = styled('div', { name, slot: 'itemOuter' })(({ theme }) => ({
  display: 'flex',
  flexDirection: 'column',
  [theme.breakpoints.down('sm')]: {
    flexDirection: 'column-reverse'
  }
}));
const Info = styled(Box, { name, slot: 'info' })(({ theme }) => ({
  color: theme.palette.text.primary,
  fontSize: theme.mixins.pxToRem(15),
  marginBottom: theme.spacing(1)
}));
const OwnerWrapper = styled('div', { name, slot: 'ownerWrapper' })(
  ({ theme }) => ({
    display: 'flex',
    justifyContent: 'space-between',
    alignItems: 'center',
    [theme.breakpoints.down('sm')]: {
      flexDirection: 'column',
      alignItems: 'flex-start'
    }
  })
);
const Owner = styled('div', { name, slot: 'owner' })(({ theme }) => ({
  overflow: 'hidden',
  padding: theme.spacing(3, 0),
  display: 'flex',
  alignItems: 'center',
  flex: 1,
  minWidth: 0,
  [theme.breakpoints.down('sm')]: {
    padding: theme.spacing(2, 0)
  }
}));

const ListingActions = styled('div', { name, slot: 'listingActions' })(
  ({ theme }) => ({
    display: 'flex',
    marginTop: theme.spacing(2),
    marginBottom: theme.spacing(2),
    flexFlow: 'wrap',
    '& button': {
      marginRight: theme.spacing(1)
    }
  })
);

const AttachmentTitle = styled('div', { name, slot: 'attachmentTitle' })(
  ({ theme }) => ({
    fontSize: theme.mixins.pxToRem(18),
    marginTop: theme.spacing(2),
    color: theme.palette.text.secondary,
    fontWeight: theme.typography.fontWeightBold
  })
);
const Attachment = styled('div', { name, slot: 'attachment' })(({ theme }) => ({
  width: '100%',
  display: 'flex',
  flexWrap: 'wrap',
  justifyContent: 'space-between',
  [theme.breakpoints.down('sm')]: {
    flexDirection: 'column'
  }
}));

const AttachmentItemWrapper = styled('div', {
  name,
  slot: 'attachmentItemWrapper'
})(({ theme }) => ({
  marginTop: theme.spacing(2),
  flexGrow: 0,
  flexShrink: 0,
  flexBasis: 'calc(50% - 8px)',
  minWidth: 300,
  maxWidth: '100%'
}));

const Expires = styled('div', {
  name,
  slot: 'Expires'
})(({ theme }) => ({
  display: 'flex',
  marginTop: theme.spacing(2),
  color: theme.palette.error.main
}));

const LocationStyled = styled(Box, { name: 'LocationStyled' })(({ theme }) => ({
  color: theme.palette.text.secondary,
  fontSize: theme.mixins.pxToRem(15),
  marginTop: theme.spacing(3),
  display: 'flex'
}));

const WrapperExternalLink = styled(Box, { name: 'WrapperExternalLink' })(
  ({ theme }) => ({
    color: theme.palette.text.secondary,
    fontSize: theme.mixins.pxToRem(15),
    marginTop: theme.spacing(1),
    display: 'flex'
  })
);

const LabelDetail = styled(Typography, { name: 'LabelDetail' })(
  ({ theme }) => ({
    fontSize: theme.mixins.pxToRem(18),
    fontWeight: theme.typography.fontWeightBold,
    color: theme.palette.grey['600'],
    marginTop: theme.spacing(1),
    marginBottom: theme.spacing(2)
  })
);

const BtnPayDisableStyled = styled(Button, { name: 'BtnPayDisable' })(
  ({ theme }) => ({
    backgroundColor: `${theme.palette.grey['100']} !important`
  })
);

const ExternalLinkStyled = styled(Link, { name: 'ExternalLink' })(
  ({ theme }) => ({
    display: 'flex',
    alignItems: 'center',
    '& .ico': {
      fontSize: theme.mixins.pxToRem(12)
    }
  })
);

const ViewMapWrapper = styled(DotSeparator, { name: 'ViewMapWrapper' })(
  ({ theme }) => ({
    fontSize: theme.typography.body2.fontSize,
    color: theme.palette.primary.main,
    '& a:hover': {
      cursor: 'pointer',
      textDecoration: 'underline'
    }
  })
);

export default function MarketplaceDetail(props: MarketplaceDetailViewProps) {
  const { item, actions, user, identity, handleAction, state, isModalView } =
    props;

  const {
    i18n,
    ItemActionMenu,
    ItemDetailInteraction,
    dispatch,
    useGetItems,
    jsxBackend,
    useIsMobile
  } = useGlobal();
  const attachments = useGetItems(item?.attachments);
  const categories = useGetItems<{ id: number; name: string }>(
    item?.categories
  );
  const isMobile = useIsMobile(true);
  const handlePayment = React.useCallback(() => {
    actions.paymentItem();
  }, [actions]);

  if (!item || !user) return null;

  const {
    is_featured,
    is_sponsor,
    title,
    price,
    url,
    description,
    short_description,
    extra,
    is_pending,
    location,
    attach_photos: images,
    user: listingSeller,
    tags,
    is_sold,
    is_expired,
    is_free,
    expires_label_detail,
    external_link
  } = item;

  const PendingCard = jsxBackend.get('core.itemView.pendingReviewCard');

  const chatWithSeller = () => {
    dispatch({
      type: 'chat/room/openChatRoom',
      payload: {
        identity: listingSeller,
        isMobile,
        text: url
      }
    });
  };

  const north = location.lat;
  const west = location.lng;
  const east = location.lng;
  const south = location.lat;

  const linkViewMap = {
    pathname: '/marketplace/search-map/',
    search: `?${BOUNDS_NORTH}=${north}&${BOUNDS_EAST}=${east}&${BOUNDS_WEST}=${west}&${BOUNDS_SOUTH}=${south}`
  };

  const canViewOnMap = CORE_GOOGLE_GOOGLE_MAP_API_KEY;

  return (
    <Block testid={`detailview ${item.resource_name}`}>
      <BlockContent>
        <Box sx={{ p: 2 }}>
          {is_pending && PendingCard ? (
            <PendingCard sxWrapper={{ mb: 1 }} item={item} />
          ) : null}
          <ItemWrapper isModalView={isModalView}>
            <ListingHeader>
              {images?.length ? (
                <Images>
                  <ListingImages images={images} alt={title} />
                </Images>
              ) : null}
              <Container>
                <Box position="relative">
                  <InnerContainer>
                    <CategoryList data={categories} />
                    <Box my={1.5} overflow="hidden">
                      <FlagWrapper>
                        <PendingFlag variant="detailView" value={is_pending} />
                        <FeaturedFlag
                          variant="detailView"
                          value={is_featured}
                        />
                        <SponsorFlag
                          variant="detailView"
                          value={is_sponsor}
                          item={item}
                        />
                      </FlagWrapper>
                      <Typography
                        component="h1"
                        variant="h3"
                        color="text.primary"
                        fontWeight="bold"
                      >
                        {title}
                      </Typography>
                    </Box>
                    <Box sx={{ display: 'flex', alignItems: 'center' }}>
                      {is_sold || is_expired ? (
                        <Box mr={1}>
                          {jsxBackend.render({
                            component: 'marketplace.ui.soldLabel',
                            props: {
                              label: is_expired ? 'expired' : ''
                            }
                          })}
                        </Box>
                      ) : null}
                      {is_free ? (
                        <Price> {i18n.formatMessage({ id: 'free' })} </Price>
                      ) : (
                        <Price children={price} />
                      )}
                    </Box>
                    {extra?.can_payment || extra?.can_invite ? (
                      <ListingActions>
                        {extra?.can_show_payment_button && (
                          <Button
                            size="medium"
                            color="primary"
                            variant="contained"
                            onClick={handlePayment}
                            startIcon={<LineIcon icon={'ico-money-bag-o'} />}
                          >
                            {i18n.formatMessage({ id: 'buy_now' })}
                          </Button>
                        )}
                        {extra?.can_show_message && (
                          <BtnPayDisableStyled
                            disabled
                            size="medium"
                            color="primary"
                            variant="contained"
                          >
                            {i18n.formatMessage({
                              id: 'no_payment_options_available'
                            })}
                          </BtnPayDisableStyled>
                        )}
                        {extra?.can_invite ? (
                          <ItemActionMenu
                            menuName="itemInviteMenu"
                            state={state}
                            handleAction={handleAction}
                            control={
                              <IconButton
                                color="primary"
                                variant={'outlined-square'}
                                size="medium"
                              >
                                <LineIcon icon={'ico-envelope'} />
                              </IconButton>
                            }
                          />
                        ) : null}
                        <ItemActionMenu
                          menuName="detailActionMenu"
                          identity={identity}
                          state={state}
                          handleAction={handleAction}
                          control={
                            <IconButton
                              color="primary"
                              variant={'outlined-square'}
                              size="medium"
                            >
                              <LineIcon icon={'ico-dottedmore-o'} />
                            </IconButton>
                          }
                        />
                      </ListingActions>
                    ) : (
                      <ItemAction
                        sx={{ position: 'absolute', top: 0, right: '-8px' }}
                      >
                        <ItemActionMenu
                          identity={identity}
                          icon={'ico-dottedmore-vertical-o'}
                          state={state}
                          menuName="detailActionMenu"
                          handleAction={handleAction}
                          size="smaller"
                        />
                      </ItemAction>
                    )}
                    <ItemOuter>
                      <Box>
                        {short_description ? (
                          <Info mt={2}>
                            <Box sx={{ whiteSpace: 'pre-wrap' }}>
                              {short_description}
                            </Box>
                          </Info>
                        ) : null}
                        {expires_label_detail ? (
                          <Expires>
                            {' '}
                            {i18n.formatMessage({
                              id: expires_label_detail
                            })}{' '}
                          </Expires>
                        ) : null}
                        <LocationStyled>
                          <LineIcon sx={{ mt: 0.25 }} icon="ico-checkin-o" />
                          <Box ml={1}>
                            <div>{location?.address}</div>
                            {location?.lat && location?.lng && (
                              <ViewMapWrapper mt={0.5}>
                                {canViewOnMap ? (
                                  <Link to={linkViewMap}>
                                    {i18n.formatMessage({
                                      id: 'view_on_map'
                                    })}
                                  </Link>
                                ) : null}
                                <Link
                                  to={`http://maps.google.com/?q=${encodeURIComponent(
                                    location?.address
                                  )}`}
                                  target="_blank"
                                >
                                  {i18n.formatMessage({
                                    id: 'view_on_google_maps'
                                  })}
                                </Link>
                              </ViewMapWrapper>
                            )}
                          </Box>
                        </LocationStyled>
                        {external_link ? (
                          <WrapperExternalLink>
                            <LineIcon
                              sx={{ mr: 1, mt: 0.25 }}
                              icon="ico-globe-alt-o"
                            />
                            <ExternalLinkStyled
                              color="primary"
                              to={external_link}
                            >
                              <TruncateText
                                component="span"
                                variant="body1"
                                lines={1}
                              >
                                {i18n.formatMessage({
                                  id: 'visit_listing_site'
                                })}
                              </TruncateText>
                              <LineIcon
                                sx={{ ml: 1 }}
                                icon="ico-external-link"
                              />
                            </ExternalLinkStyled>
                          </WrapperExternalLink>
                        ) : null}
                      </Box>
                      <OwnerWrapper>
                        <Owner>
                          <AuthorInfo item={item} sx={{ mt: 0 }} />
                        </Owner>
                        {extra?.can_message ? (
                          <Button
                            data-testid="itemActionMessage"
                            size="medium"
                            color="primary"
                            variant="outlined"
                            onClick={chatWithSeller}
                            startIcon={<LineIcon icon={'ico-comment-o'} />}
                          >
                            {i18n.formatMessage({ id: 'messages' })}
                          </Button>
                        ) : null}
                      </OwnerWrapper>
                    </ItemOuter>
                  </InnerContainer>
                </Box>
              </Container>
            </ListingHeader>
            <Box>
              <LabelDetail>
                {i18n.formatMessage({ id: 'listing_detail' })}
              </LabelDetail>
              {description ? (
                <Info>
                  <HtmlViewerWrapper>
                    <HtmlViewer html={description || ''} />
                  </HtmlViewerWrapper>
                </Info>
              ) : null}
              <Tags tags={tags} module_name={item?.module_name} />
              {attachments?.length > 0 && (
                <>
                  <AttachmentTitle>
                    {i18n.formatMessage({ id: 'attachments' })}
                  </AttachmentTitle>
                  <Attachment>
                    {attachments.map((item, index) => (
                      <AttachmentItemWrapper key={item.id.toString()}>
                        <AttachmentItem
                          fileName={item.file_name}
                          downloadUrl={item.download_url}
                          isImage={item.is_image}
                          fileSizeText={item.file_size_text}
                          size="large"
                          image={item?.image}
                          identity={item?._identity}
                          index={index}
                        />
                      </AttachmentItemWrapper>
                    ))}
                  </Attachment>
                </>
              )}
              <ItemDetailInteraction
                identity={identity}
                state={state}
                handleAction={handleAction}
              />
            </Box>
          </ItemWrapper>
        </Box>
      </BlockContent>
    </Block>
  );
}

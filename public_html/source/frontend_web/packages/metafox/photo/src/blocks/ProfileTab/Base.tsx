import { useGetItem, useGlobal } from '@metafox/framework';
import { UIBlockViewProps } from '@metafox/ui';

export default function ProfileTabPhoto(props: UIBlockViewProps) {
  const { jsxBackend, usePageParams, useSession, i18n } = useGlobal();
  const { user: userLogin } = useSession();
  const pageParams = usePageParams();
  const { identity } = pageParams;
  const authorUser = useGetItem(identity);

  const labelPhotoOfUser =
    authorUser?.id === userLogin?.id
      ? 'tab_photos_of_you'
      : i18n.formatMessage(
          { id: 'tab_photo_of_user' },
          { user: authorUser.full_name }
        );

  const labelYourPhoto =
    authorUser?.id === userLogin?.id
      ? 'tab_your_photos'
      : i18n.formatMessage(
          { id: 'tab_user_s_photos' },
          { user: authorUser.full_name }
        );

  return jsxBackend.render({
    component: 'core.block.tabContainer',
    props: {
      ...props,
      tabProps: {
        tabs: [
          {
            placeholderSearch: 'search_photos',
            id: 3,
            label: labelPhotoOfUser,
            tab: 'photo_of_user'
          },
          {
            placeholderSearch: 'search_photos',
            id: 1,
            label: labelYourPhoto,
            tab: 'photos'
          },
          {
            placeholderSearch: 'search_albums',
            id: 2,
            label: 'tab_photo_albums',
            tab: 'albums'
          }
        ]
      }
    }
  });
}

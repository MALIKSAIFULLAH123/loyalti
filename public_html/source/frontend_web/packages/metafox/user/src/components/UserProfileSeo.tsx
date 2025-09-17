/**
 * @type: ui
 * name: user.profile.seo
 */
import { useGlobal } from '@metafox/framework';

function UserProfileSeo() {
  const { useSubject } = useGlobal();
  const subject = useSubject();

  if (!subject) {
    return null;
  }

  return null;
}

export default UserProfileSeo;

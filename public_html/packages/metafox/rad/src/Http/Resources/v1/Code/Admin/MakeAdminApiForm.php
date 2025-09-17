<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Rad\Http\Resources\v1\Code\Admin;

/**
 * Class MakeAdminApiForm.
 * @ignore
 * @codeCoverageIgnore
 */
class MakeAdminApiForm extends MakeWebApiForm
{
    protected function prepare(): void
    {
        $this
            ->title('Generate Admin Apis')
            ->action('admincp/rad/code/make/admin_api')
            ->asPost()
            ->setValue([
                'package'     => 'metafox/core',
                '--overwrite' => false,
                '--ver'       => 'v1',
                '--dry'       => false,
                '--admin'     => true,
                '--test'      => false,
            ]);
    }
}

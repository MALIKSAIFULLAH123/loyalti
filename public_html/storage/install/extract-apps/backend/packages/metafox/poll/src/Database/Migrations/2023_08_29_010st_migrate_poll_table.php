<?php

/*
 * stub: /packages/database/migration.stub
 */

/*
 * @ignore
 * @codeCoverageIgnore
 * @link \$PACKAGE_NAMESPACE$\Models
 */

use MetaFox\Poll\Support\Facade\Poll;

return new class () extends \MetaFox\Platform\Support\ContentMigration {
    protected string $modelName = \MetaFox\Poll\Models\Poll::class;
};

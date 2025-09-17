<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Saved\Tests\Unit\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\Saved\Models\Saved;
use Tests\TestCases\TestEntityModel;

class SavedTest extends TestEntityModel
{
    public function modelName(): string
    {
        return Saved::class;
    }

    public function makeOne($user)
    {
        $this->actingAs($user);

        return Saved::factory()->setUser($user)
            ->makeOne([
                'item_type' => 'activity_post',
                'item_id'   => 0,
            ]);
    }

    /**
     * @param $model
     * @return void
     * @depends testFindById
     */
    public function testValidateStored($model)
    {
        $this->assertInstanceOf(Saved::class, $model);
        $this->assertInstanceOf(ContractUser::class, $model->user);
        $this->assertInstanceOf(ContractUser::class, $model->owner);

        // must have save list
        $this->assertInstanceOf(BelongsToMany::class, $model->savedLists());
        $this->assertEmpty($model->savedLists);

        // validate item

        $this->assertInstanceOf(BelongsTo::class, $model->item());
        $this->assertEmpty($model->item);
    }
}

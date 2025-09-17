<?php

namespace MetaFox\BackgroundStatus\Tests\Unit\Http\Requests\v1;

use MetaFox\BackgroundStatus\Http\Requests\v1\IndexRequest;
use Tests\TestFormRequest;

/**
 * Class IndexRequestTest.
 */
class IndexRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return IndexRequest::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->withSampleParameters('page', 'limit')
        );
    }

    public function testSuccess()
    {
        $form = $this->buildForm([]);
        $form->validateResolved();

        $this->assertNotEmpty($form->validated());
    }
}

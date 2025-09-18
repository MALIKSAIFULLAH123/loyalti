<?php

namespace MetaFox\Poll\Tests\Unit\Http\Requests\v1\Poll;

use Illuminate\Validation\ValidationException;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Poll\Http\Requests\v1\Poll\UpdateRequest as Request;
use MetaFox\Poll\Rules\UpdateBannerRule;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for test
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Poll\Http\Controllers\Api\PollController::$controllers;
 * stub: api_action_request_test.stub
 */

/**
 * Class UpdateRequestTest.
 */
class UpdateRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return Request::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            // todo check if have errors?
//            $this->validate()->shouldHaveError('question', 'privacy'),
            $this->failIf('question', 0, null, [], 'A', str_pad('A', 1000, 'A')),
            $this->failIf('answers', null, [], [
                ['answer' => null],
                ['answer' => str_pad('A', 200, 'A')],
            ])
        );
    }

    public function testSuccess()
    {
        $form = $this->buildForm([
            'question' => $this->faker->title,
            'answers'  => [
                ['answer' => $this->faker->words(5, true)],
                ['answer' => $this->faker->words(5, true)],
            ],
            'text'        => $this->faker->text,
            'is_multiple' => 1,
            'public_vote' => 0,
            'randomize'   => 0,
            'privacy'     => MetaFoxPrivacy::EVERYONE,
        ]);
        $form->validateResolved();
        $this->assertNotEmpty($form->validated());
    }

    public function testTextNullable()
    {
        $form = $this->buildForm([
            'question' => $this->faker->title,
            'answers'  => [
                ['answer' => $this->faker->words(5, true)],
                ['answer' => $this->faker->words(5, true)],
            ],
            'text'        => null,
            'is_multiple' => 1,
            'public_vote' => 0,
            'randomize'   => 0,
            'privacy'     => MetaFoxPrivacy::EVERYONE,
        ]);
        $form->validateResolved();
        $this->assertNotEmpty($form->validated());
    }

    public function testSuccessWithEnableCloseUnchecked()
    {
        $form = $this->buildForm([
            'question' => $this->faker->title,
            'answers'  => [
                ['answer' => $this->faker->words(5, true)],
                ['answer' => $this->faker->words(5, true)],
            ],
            'text'         => $this->faker->text,
            'is_multiple'  => 1,
            'public_vote'  => 0,
            'randomize'    => 0,
            'enable_close' => 0,
            'close_time'   => now()->addDays(10),
            'privacy'      => MetaFoxPrivacy::EVERYONE,
        ]);
        $form->validateResolved();
        $data = $form->validated();
        $this->assertNotEmpty($data);
        $this->assertNull($data['closed_at']);
    }

    public function testSuccessWithCloseTime()
    {
        $form = $this->buildForm([
            'question' => $this->faker->title,
            'answers'  => [
                ['answer' => $this->faker->words(5, true)],
                ['answer' => $this->faker->words(5, true)],
            ],
            'text'         => $this->faker->text,
            'is_multiple'  => 1,
            'public_vote'  => 0,
            'randomize'    => 0,
            'enable_close' => 1,
            'close_time'   => now()->addDays(10),
            'privacy'      => MetaFoxPrivacy::EVERYONE,
        ]);
        $form->validateResolved();
        $data = $form->validated();
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('closed_at', $data);
    }

    public function testQuestionString()
    {
        $form = $this->buildForm([
            'question' => 1,
            'text'     => $this->faker->text,
            'answers'  => [
                ['answer' => $this->faker->words(5, true)],
                ['answer' => $this->faker->words(5, true)],
            ],
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testQuestionMin()
    {
        $form = $this->buildForm([
            'question' => 'T',
            'text'     => $this->faker->text,
            'answers'  => [
                ['answer' => $this->faker->words(5, true)],
                ['answer' => $this->faker->words(5, true)],
            ],
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testQuestionMax()
    {
        $form = $this->buildForm([
            'question' => $this->faker->realTextBetween(256, 300),
            'text'     => $this->faker->text,
            'answers'  => [
                ['answer' => $this->faker->words(5, true)],
                ['answer' => $this->faker->words(5, true)],
            ],
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testAnswersArray()
    {
        $form = $this->buildForm([
            'question' => $this->faker->title,
            'answers'  => 1,
            'text'     => $this->faker->text,
            'privacy'  => MetaFoxPrivacy::EVERYONE,
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testAnswersMin()
    {
        $form = $this->buildForm([
            'question' => $this->faker->title,
            'text'     => $this->faker->text,
            'answers'  => [
                ['answer' => $this->faker->sentence],
            ],
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testAnswersValueUnique()
    {
        $answerValue = $this->faker->words(5, true);
        $form        = $this->buildForm([
            'question' => $this->faker->title,
            'text'     => $this->faker->text,
            'answers'  => [
                ['answer' => $answerValue],
                ['answer' => $answerValue],
            ],
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testAnswersValueRequired()
    {
        $form = $this->buildForm([
            'question' => $this->faker->title,
            'text'     => $this->faker->text,
            'answers'  => [
                [$this->faker->words(5, true)],
                [$this->faker->words(5, true)],
            ],
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testItemIdExist()
    {
        $form = $this->buildForm([
            'question' => $this->faker->title,
            'text'     => $this->faker->text,
            'item_id'  => [0],
            'answers'  => [
                ['answer' => $this->faker->words(5, true)],
                ['answer' => $this->faker->words(5, true)],
            ],
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testItemIdNumeric()
    {
        $form = $this->buildForm([
            'question' => $this->faker->title,
            'text'     => $this->faker->text,
            'answers'  => [
                ['answer' => $this->faker->words(5, true)],
                ['answer' => $this->faker->words(5, true)],
            ],
            'item_id' => ['Test'],
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testFileArray()
    {
        $form = $this->buildForm([
            'title'    => $this->faker->sentence,
            'question' => $this->faker->title,
            'answers'  => [
                ['answer' => $this->faker->words(5, true)],
                ['answer' => $this->faker->words(5, true)],
            ],
            'text'    => $this->faker->text,
            'file'    => 's',
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);
        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testFileTempFileRequiredWithFile()
    {
        $form = $this->buildForm([
            'title'    => $this->faker->sentence,
            'question' => $this->faker->title,
            'answers'  => [
                ['answer' => $this->faker->words(5, true)],
                ['answer' => $this->faker->words(5, true)],
            ],
            'text' => $this->faker->text,
            'file' => [
                'test' => 'test',
            ],
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);
        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testFileTempFileNumeric()
    {
        $form = $this->buildForm([
            'title'    => $this->faker->sentence,
            'question' => $this->faker->title,
            'text'     => $this->faker->text,
            'answers'  => [
                ['answer' => $this->faker->words(5, true)],
                ['answer' => $this->faker->words(5, true)],
            ],
            'file' => [
                'temp_file' => 's',
            ],
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);
        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testFileTempFileExist()
    {
        $form = $this->buildForm([
            'question' => $this->faker->title,
            'text'     => $this->faker->text,
            'answers'  => [
                ['answer' => $this->faker->words(5, true)],
                ['answer' => $this->faker->words(5, true)],
            ],
            'file' => [
                'temp_file' => 0,
            ],
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);
        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testTextString()
    {
        $form = $this->buildForm([
            'question' => $this->faker->title,
            'answers'  => [
                ['answer' => $this->faker->words(5, true)],
                ['answer' => $this->faker->words(5, true)],
            ],
            'text'    => 0,
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testBackgroundString()
    {
        $form = $this->buildForm([
            'question' => $this->faker->title,
            'answers'  => [
                ['answer' => $this->faker->words(5, true)],
                ['answer' => $this->faker->words(5, true)],
            ],
            'text'       => 0,
            'background' => 1,
            'privacy'    => MetaFoxPrivacy::EVERYONE,
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testBackgroundIsHexColorCode()
    {
        $form = $this->buildForm([
            'question' => $this->faker->title,
            'answers'  => [
                ['answer' => $this->faker->words(5, true)],
                ['answer' => $this->faker->words(5, true)],
            ],
            'text'       => 0,
            'background' => 'NON_HEX_COLOR_CODE',
            'privacy'    => MetaFoxPrivacy::EVERYONE,
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testPercentageString()
    {
        $form = $this->buildForm([
            'question' => $this->faker->title,
            'answers'  => [
                ['answer' => $this->faker->words(5, true)],
                ['answer' => $this->faker->words(5, true)],
            ],
            'text'       => 0,
            'percentage' => 1,
            'privacy'    => MetaFoxPrivacy::EVERYONE,
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testPercentageIsHexColorCode()
    {
        $form = $this->buildForm([
            'question' => $this->faker->title,
            'answers'  => [
                ['answer' => $this->faker->words(5, true)],
                ['answer' => $this->faker->words(5, true)],
            ],
            'text'       => 0,
            'percentage' => 'NON_HEX_COLOR_CODE',
            'privacy'    => MetaFoxPrivacy::EVERYONE,
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testBorderString()
    {
        $form = $this->buildForm([
            'question' => $this->faker->title,
            'answers'  => [
                ['answer' => $this->faker->words(5, true)],
                ['answer' => $this->faker->words(5, true)],
            ],
            'text'    => 0,
            'border'  => 1,
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testBorderIsHexColorCode()
    {
        $form = $this->buildForm([
            'question' => $this->faker->title,
            'answers'  => [
                ['answer' => $this->faker->words(5, true)],
                ['answer' => $this->faker->words(5, true)],
            ],
            'text'    => 0,
            'border'  => 'NON_HEX_COLOR_CODE',
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testEnableCloseNumeric()
    {
        $form = $this->buildForm([
            'question' => $this->faker->title,
            'answers'  => [
                ['answer' => $this->faker->words(5, true)],
                ['answer' => $this->faker->words(5, true)],
            ],
            'text'         => $this->faker->text,
            'enable_close' => 'test',
            'privacy'      => MetaFoxPrivacy::EVERYONE,
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testEnableCloseInAllow()
    {
        $form = $this->buildForm([
            'question' => $this->faker->title,
            'answers'  => [
                ['answer' => $this->faker->words(5, true)],
                ['answer' => $this->faker->words(5, true)],
            ],
            'text'         => $this->faker->text,
            'enable_close' => 2,
            'privacy'      => MetaFoxPrivacy::EVERYONE,
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testCloseTimeRequiredWithEnableClose()
    {
        $form = $this->buildForm([
            'question' => $this->faker->title,
            'answers'  => [
                ['answer' => $this->faker->words(5, true)],
                ['answer' => $this->faker->words(5, true)],
            ],
            'text'         => $this->faker->text,
            'enable_close' => 1,
            'privacy'      => MetaFoxPrivacy::EVERYONE,
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testCloseTimeNumeric()
    {
        $form = $this->buildForm([
            'question' => $this->faker->title,
            'answers'  => [
                ['answer' => $this->faker->words(5, true)],
                ['answer' => $this->faker->words(5, true)],
            ],
            'text'         => $this->faker->text,
            'enable_close' => 1,
            'close_time'   => 's',
            'privacy'      => MetaFoxPrivacy::EVERYONE,
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testIsMultipleNumeric()
    {
        $form = $this->buildForm([
            'question' => $this->faker->title,
            'answers'  => [
                ['answer' => $this->faker->words(5, true)],
                ['answer' => $this->faker->words(5, true)],
            ],
            'text'        => $this->faker->text,
            'is_multiple' => 'test',
            'privacy'     => MetaFoxPrivacy::EVERYONE,
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testIsMultipleInAllow()
    {
        $form = $this->buildForm([
            'question' => $this->faker->title,
            'answers'  => [
                ['answer' => $this->faker->words(5, true)],
                ['answer' => $this->faker->words(5, true)],
            ],
            'text'        => $this->faker->text,
            'is_multiple' => 2,
            'privacy'     => MetaFoxPrivacy::EVERYONE,
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testHideVoteNumeric()
    {
        $form = $this->buildForm([
            'question' => $this->faker->title,
            'answers'  => [
                ['answer' => $this->faker->words(5, true)],
                ['answer' => $this->faker->words(5, true)],
            ],
            'text'        => $this->faker->text,
            'public_vote' => 'test',
            'privacy'     => MetaFoxPrivacy::EVERYONE,
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testHideVoteInAllow()
    {
        $form = $this->buildForm([
            'question' => $this->faker->title,
            'answers'  => [
                ['answer' => $this->faker->words(5, true)],
                ['answer' => $this->faker->words(5, true)],
            ],
            'text'        => $this->faker->text,
            'public_vote' => 2,
            'privacy'     => MetaFoxPrivacy::EVERYONE,
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testRandomizeNumeric()
    {
        $form = $this->buildForm([
            'question' => $this->faker->title,
            'answers'  => [
                ['answer' => $this->faker->words(5, true)],
                ['answer' => $this->faker->words(5, true)],
            ],
            'text'      => $this->faker->text,
            'randomize' => 'test',
            'privacy'   => MetaFoxPrivacy::EVERYONE,
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testRandomizeInAllow()
    {
        $form = $this->buildForm([
            'question' => $this->faker->title,
            'answers'  => [
                ['answer' => $this->faker->words(5, true)],
                ['answer' => $this->faker->words(5, true)],
            ],
            'text'      => $this->faker->text,
            'randomize' => 2,
            'privacy'   => MetaFoxPrivacy::EVERYONE,
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testPrivacyIn()
    {
        $form = $this->buildForm([
            'question' => $this->faker->title,
            'answers'  => [
                ['answer' => $this->faker->words(5, true)],
                ['answer' => $this->faker->words(5, true)],
            ],
            'text'    => $this->faker->text,
            'privacy' => -1,
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testRemoveBannerWhenImageIsRequired()
    {
        $this->markTestIncomplete();
        $stub = $this->getMockBuilder(UpdateBannerRule::class)
            ->onlyMethods(['isImageRequired'])
            ->getMock();

        $stub->method('isImageRequired')
            ->willReturn(true);

        $this->assertTrue($stub->isImageRequired());

        $form = $this->buildForm([
            'file' => [
                'temp_file' => 0,
                'status'    => 'remove',
            ],
        ], $stub);

        $this->expectException(ValidationException::class);

        $form->validateResolved();
    }

    public function testUpdateWithoutUpdateImageWhenImageIsRequired()
    {
        $stub = $this->getMockBuilder(UpdateBannerRule::class)
            ->onlyMethods(['isImageRequired'])
            ->getMock();

        $stub->method('isImageRequired')
            ->willReturn(true);

        $this->assertTrue($stub->isImageRequired());

        $form = $this->buildForm([
            'question' => $this->faker->title,
        ], $stub);

        $form->validateResolved();
        $data = $form->validated();
        $this->assertNotEmpty($data);
    }

    public function testUpdateWithoutUpdateImageWhenImageIsRequiredAndNotHasBanner()
    {
        $this->markTestIncomplete();
        $stub = $this->getMockBuilder(UpdateBannerRule::class)
            ->onlyMethods(['isImageRequired'])
            ->getMock();

        $stub->method('isImageRequired')
            ->willReturn(true);

        $this->assertTrue($stub->isImageRequired());

        $form = $this->buildForm([
            'question'   => $this->faker->title,
            'has_banner' => 0,
        ], $stub);
        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }
}

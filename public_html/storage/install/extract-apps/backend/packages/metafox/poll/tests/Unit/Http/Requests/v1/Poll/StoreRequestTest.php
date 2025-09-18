<?php

namespace MetaFox\Poll\Tests\Unit\Http\Requests\v1\Poll;

use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Poll\Http\Requests\v1\Poll\StoreRequest as Request;
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
 * Class StoreRequestTest.
 * @property \MetaFox\User\Models\User $user
 */
class StoreRequestTest extends TestFormRequest
{
    public const TEST_MIN_QUESTION_LENGTH = 10;
    public const TEST_MAX_QUESTION_LENGTH = 100;

    public function requestName(): string
    {
        return Request::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('question', 'privacy'),
            $this->failIf('question', 0, null, [], 'A', str_pad('A', 1000, 'A')),
            $this->failIf('answers', null, [], [
                ['answer' => null],
                ['answer' => str_pad('A', 200, 'A')],
            ])
        );
    }

    public function getValidQuestionName(): string
    {
        return $this->faker->realTextBetween(self::TEST_MIN_QUESTION_LENGTH, self::TEST_MAX_QUESTION_LENGTH);
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->user = $this->asAdminUser();
        $this->be($this->user);

        $this->mockSiteSettings([
            'poll.minimum_name_length'                                           => self::TEST_MIN_QUESTION_LENGTH,
            'poll.maximum_name_length'                                           => self::TEST_MAX_QUESTION_LENGTH,
            'core.attachment.maximum_number_of_attachments_that_can_be_uploaded' => 1,
            'poll.is_image_required'                                             => false,
        ]);
    }

    public function testSuccess()
    {
        $form = $this->buildForm([
            'question' => $this->getValidQuestionName(),
            'answers'  => [
                ['answer' => $this->faker->words(5, true)],
                ['answer' => $this->faker->words(5, true)],
            ],
            'text'        => $this->faker->text,
            'is_multiple' => 1,
            'public_vote' => 1,
            'randomize'   => 0,
            'privacy'     => MetaFoxPrivacy::EVERYONE,
        ]);
        $form->validateResolved();
        $this->assertNotEmpty($form->validated());
    }

    public function testSuccessWithEnableCloseUnchecked()
    {
        $form = $this->buildForm([
            'question' => $this->getValidQuestionName(),
            'answers'  => [
                ['answer' => $this->faker->words(5, true)],
                ['answer' => $this->faker->words(5, true)],
            ],
            'text'         => $this->faker->words(3, true),
            'is_multiple'  => 1,
            'public_vote'  => 1,
            'randomize'    => 0,
            'enable_close' => 0,
            'close_time'   => now()->addDays(10),
            'privacy'      => MetaFoxPrivacy::EVERYONE,
        ]);
        $form->validateResolved();
        $data = $form->validated();
        $this->assertNotEmpty($data);
        $this->assertArrayNotHasKey('closed_at', $data);
    }

    public function testSuccessWithCloseTime()
    {
        $form = $this->buildForm([
            'question' => $this->getValidQuestionName(),
            'answers'  => [
                ['answer' => uniqid('poll answer ')],
                ['answer' => uniqid('poll answer ')],
            ],
            'text'         => $this->faker->words(3, true),
            'is_multiple'  => 1,
            'public_vote'  => 1,
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

    public function testSuccessWithTextNull()
    {
        $form = $this->buildForm([
            'question' => $this->getValidQuestionName(),
            'answers'  => [
                ['answer' => uniqid('poll answer ')],
                ['answer' => uniqid('poll answer ')],
            ],
            'text'    => '',
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);

        $form->validateResolved();
        $data = $form->validated();
        $this->assertNotEmpty($data);
    }

    public function testAnswersRequired()
    {
        $form = $this->buildForm([
            'question' => $this->getValidQuestionName(),
            'privacy'  => MetaFoxPrivacy::EVERYONE,
            'text'     => $this->faker->text,
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testAnswersArray()
    {
        $form = $this->buildForm([
            'question' => $this->getValidQuestionName(),
            'answers'  => 1,
            'text'     => $this->faker->words(3, true),
            'privacy'  => MetaFoxPrivacy::EVERYONE,
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testAnswersMin()
    {
        $form = $this->buildForm([
            'question' => $this->getValidQuestionName(),
            'text'     => $this->faker->text,
            'answers'  => [
                ['answer' => uniqid('poll answer ')],
            ],
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testInvalidLongAnswer()
    {
        $form = $this->buildForm([
            'question' => $this->getValidQuestionName(),
            'text'     => $this->faker->text,
            'answers'  => [
                ['answer' => $this->faker->sentence],
                [
                    'answer' => $this->faker->realTextBetween(
                        Request::MAX_ANSWER_LENGTH,
                        Request::MAX_ANSWER_LENGTH + 100
                    ),
                ],
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
            'question' => $this->getValidQuestionName(),
            'text'     => $this->faker->words(3, true),
            'answers'  => [
                ['answer' => $answerValue],
                ['answer' => $answerValue],
            ],
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
        $form->validated();
    }

    public function testAnswersValueRequired()
    {
        $form = $this->buildForm([
            'question' => $this->getValidQuestionName(),
            'text'     => $this->faker->words(3, true),
            'answers'  => [
                [$this->faker->words(5, true)],
                [$this->faker->words(5, true)],
            ],
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testOwnerIdExist()
    {
        $form = $this->buildForm([
            'question' => $this->getValidQuestionName(),
            'text'     => $this->faker->words(3, true),
            'owner_id' => [0],
            'answers'  => [
                ['answer' => $this->faker->words(5, true)],
                ['answer' => $this->faker->words(5, true)],
            ],
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testFileArray()
    {
        $form = $this->buildForm([
            'title'    => $this->faker->sentence,
            'question' => $this->getValidQuestionName(),
            'answers'  => [
                ['answer' => $this->faker->words(5, true)],
                ['answer' => $this->faker->words(5, true)],
            ],
            'text'    => $this->faker->words(3, true),
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
            'question' => $this->getValidQuestionName(),
            'answers'  => [
                ['answer' => $this->faker->words(5, true)],
                ['answer' => $this->faker->words(5, true)],
            ],
            'text' => $this->faker->words(3, true),
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
            'question' => $this->getValidQuestionName(),
            'text'     => $this->faker->words(3, true),
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
            'question' => $this->getValidQuestionName(),
            'text'     => $this->faker->words(3, true),
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
            'question' => $this->getValidQuestionName(),
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
            'question' => $this->getValidQuestionName(),
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
            'question' => $this->getValidQuestionName(),
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
            'question' => $this->getValidQuestionName(),
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
            'question' => $this->getValidQuestionName(),
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
            'question' => $this->getValidQuestionName(),
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
            'question' => $this->getValidQuestionName(),
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
            'question' => $this->getValidQuestionName(),
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
            'question' => $this->getValidQuestionName(),
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
            'question' => $this->getValidQuestionName(),
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
            'question' => $this->getValidQuestionName(),
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

    public function testCloseTimeNotInPast()
    {
        $form = $this->buildForm([
            'question' => $this->getValidQuestionName(),
            'answers'  => [
                ['answer' => $this->faker->words(5, true)],
                ['answer' => $this->faker->words(5, true)],
            ],
            'text'         => $this->faker->text,
            'enable_close' => 1,
            'close_time'   => Carbon::now()->subMinutes(5),
            'privacy'      => MetaFoxPrivacy::EVERYONE,
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testIsMultipleNumeric()
    {
        $form = $this->buildForm([
            'question' => $this->getValidQuestionName(),
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
            'question' => $this->getValidQuestionName(),
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
            'question' => $this->getValidQuestionName(),
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
            'question' => $this->getValidQuestionName(),
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
            'question' => $this->getValidQuestionName(),
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
            'question' => $this->getValidQuestionName(),
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

    public function testPrivacyRequired()
    {
        $form = $this->buildForm([
            'question' => $this->getValidQuestionName(),
            'answers'  => [
                ['answer' => $this->faker->words(5, true)],
                ['answer' => $this->faker->words(5, true)],
            ],
            'text'    => $this->faker->text,
            'privacy' => null,
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testPrivacyIn()
    {
        $form = $this->buildForm([
            'question' => $this->getValidQuestionName(),
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
}

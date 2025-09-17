<?php

namespace MetaFox\Quiz\Tests\Unit\Http\Resources\v1\Quiz;

use MetaFox\Form\AbstractForm;
use MetaFox\Quiz\Http\Resources\v1\Quiz\EditQuizForm as Form;
use MetaFox\Quiz\Models\Quiz as Model;
use Tests\TestCase;

class QuizEditFormTest extends TestCase
{
    public function testInstance()
    {
        $data = new Model();
        $form = new Form($data);
        $this->assertInstanceOf(AbstractForm::class, $form);
    }
}

<?php

namespace MetaFox\Form\Mobile;

class BirthdayField extends DateTimeField
{
    public function initialize(): void
    {
        $this->setComponent('Birthday')
            ->maxWidth('220px')
            ->datePickerMode('date')
            ->displayFormat('DD/MM/YYYY');
    }
}

<?php

namespace MetaFox\Form;

/**
 * Interface FormField.
 */
interface FormField
{
    /**
     * @return array
     */
    public function toArray(): array;

    /**
     * Assign field name.
     *
     * @param string $name
     *
     * @return $this
     */
    public function name(string $name): self;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return mixed
     */
    public function getValue();

    /**
     * @return AbstractForm|null
     */
    public function getForm(): ?AbstractForm;

    /**
     * @param AbstractForm $form
     */
    public function setForm(AbstractForm $form): void;

    public function describe(): array;
}

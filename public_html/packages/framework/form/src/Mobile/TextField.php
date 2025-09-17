<?php

namespace MetaFox\Form\Mobile;

use MetaFox\Form\AbstractField;

/**
 * @link https://reactnative.dev/docs/textinput
 */
class TextField extends AbstractField
{
    public function initialize(): void
    {
        $this->setComponent('Text');
    }

    /**
     * @link https://reactnative.dev/docs/textinput#autocapitalize
     * @param  bool  $bool
     * @return $this
     */
    public function allowFontScaling(bool $bool = true): self
    {
        return $this->setAttribute('allowFontScaling', $bool);
    }

    /**
     * @link https://reactnative.dev/docs/textinput#autocapitalize
     * @param  string $string
     * @return $this
     */
    public function autoCapitalize(string $string): self
    {
        return $this->setAttribute('autoCapitalize', $string);
    }

    /**
     * @link https://reactnative.dev/docs/textinput#bluronsubmit
     *
     * @param  bool  $bool
     * @return $this
     */
    public function blurOnSubmit(bool $bool = true): self
    {
        return $this->setAttribute('blurOnSubmit', $bool);
    }

    /**
     * @link https://reactnative.dev/docs/textinput#clearbuttonmode-ios
     *
     * @param  string $string
     * @return $this
     */
    public function clearButtonMode(string $string = 'never'): self
    {
        return $this->setAttribute('clearButtonMode', $string);
    }

    /**
     * @link https://reactnative.dev/docs/textinput#cleartextonfocus-ios
     *
     * @param  bool  $bool
     * @return $this
     */
    public function clearTextOnFocus(bool $bool = false): self
    {
        return $this->setAttribute('clearTextOnFocus', $bool);
    }

    /**
     * @link https://reactnative.dev/docs/textinput#contextmenuhidden
     *
     * @param  bool  $bool
     * @return $this
     */
    public function contextMenuHidden(bool $bool = false): self
    {
        return $this->setAttribute('contextMenuHidden', $bool);
    }

    /**
     * @link https://reactnative.dev/docs/textinput#datadetectortypes-ios
     *
     * @param  string $type
     * @return $this
     */
    public function dataDetectorTypes(string $type): self
    {
        return $this->setAttribute('dataDetectorTypes', $type);
    }

    /**
     * @link https://reactnative.dev/docs/textinput#disablefullscreenui-android
     *
     * @param  bool  $bool
     * @return $this
     */
    public function disableFullscreenUI(bool $bool = false): self
    {
        return $this->setAttribute('disableFullscreenUI', $bool);
    }

    /**
     * @link https://reactnative.dev/docs/textinput#editable
     *
     * @param  bool  $bool
     * @return $this
     */
    public function editable(bool $bool = false): self
    {
        return $this->setAttribute('editable', $bool);
    }

    /**
     * @link https://reactnative.dev/docs/textinput#enablesreturnkeyautomatically-ios
     *
     * @param  string $string
     * @return $this
     */
    public function enablesReturnKeyAutomatically(string $string): self
    {
        return $this->setAttribute('enablesReturnKeyAutomatically', $string);
    }

    /**
     * @link https://reactnative.dev/docs/textinput#importantforautofill-android
     *
     * @param  string $string
     * @return $this
     */
    public function importantForAutofill(string $string): self
    {
        return $this->setAttribute('importantForAutofill', $string);
    }

    /**
     * @link https://reactnative.dev/docs/textinput#inlineimageleft-android
     *
     * @param  string $string
     * @return $this
     */
    public function inlineImageLeft(string $string): self
    {
        return $this->setAttribute('inlineImageLeft', $string);
    }

    /**
     * @link https://reactnative.dev/docs/textinput#inlineimagepadding-android
     *
     * @param  int   $int
     * @return $this
     */
    public function inlineImagePadding(int $int): self
    {
        return $this->setAttribute('inlineImagePadding', $int);
    }

    /**
     * @link https://reactnative.dev/docs/textinput#keyboardappearance-ios
     *
     * @param  string $keyboard
     * @return $this
     */
    public function keyboardAppearance(string $keyboard): self
    {
        return $this->setAttribute('keyboardAppearance', $keyboard);
    }

    /**
     * @link https://reactnative.dev/docs/textinput#keyboardtype
     *
     * @param  string $keyboardType
     * @return $this
     */
    public function keyboardType(string $keyboardType): self
    {
        return $this->setAttribute('keyboardType', $keyboardType);
    }

    /**
     * @link https://reactnative.dev/docs/textinput#multiline
     *
     * @param  bool  $value
     * @return $this
     */
    public function multiline(bool $value = true): self
    {
        return $this->setAttribute('multiline', $value);
    }

    /**
     * @link https://reactnative.dev/docs/textinput#numberoflines-android
     *
     * @param  int   $numberOfLines
     * @return $this
     */
    public function numberOfLines(int $numberOfLines): self
    {
        return $this->setAttribute('numberOfLines', $numberOfLines);
    }

    /**
     * @link https://reactnative.dev/docs/textinput#placeholdertextcolor
     *
     * @param  string $color
     * @return $this
     */
    public function placeholderTextColor(string $color): self
    {
        return $this->setAttribute('placeholderTextColor', $color);
    }

    /**
     * @link https://reactnative.dev/docs/textinput#returnkeylabel-android
     *
     * @param  string $color
     * @return $this
     */
    public function returnKeyLabel(string $color): self
    {
        return $this->setAttribute('returnKeyLabel', $color);
    }

    /**
     * @link https://reactnative.dev/docs/textinput#spellcheck-ios
     *
     * @param  bool  $value
     * @return $this
     */
    public function spellCheck(bool $value = true): self
    {
        return $this->setAttribute('spellCheck', $value);
    }

    /**
     * @link https://reactnative.dev/docs/textinput#textalign
     *
     * @param  string $textAlign
     * @return $this
     */
    public function textAlign(string $textAlign): self
    {
        return $this->setAttribute('textAlign', $textAlign);
    }

    /**
     * @link https://reactnative.dev/docs/textinput#textalign
     *
     * @param  string $textContentType
     * @return $this
     */
    public function textContentType(string $textContentType): self
    {
        return $this->setAttribute('textContentType', $textContentType);
    }

    public function delayTime(int $time): self
    {
        return $this->setAttribute('delayTime', $time);
    }

    public function asNumber(): self
    {
        return $this->setAttribute('keyboardType', 'numeric');
    }

    public function validateAction(string $action): self
    {
        return $this->setAttribute('validateAction', $action);
    }
}

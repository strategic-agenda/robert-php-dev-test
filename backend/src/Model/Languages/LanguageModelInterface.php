<?php

namespace Intobi\Model\Languages;

interface LanguageModelInterface
{
    const LANGUAGE_CODE = 'language_code';
    const LANGUAGE_NAME = 'language_name';

    /**
     * @param string $languageName
     * @return LanguageModelInterface
     */
    public function setLanguageName(string $languageName): LanguageModelInterface;

    /**
     * @param string $languageCode
     * @return LanguageModelInterface
     */
    public function setLanguageCode(string $languageCode): LanguageModelInterface;

    /**
     * @return string
     */
    public function getLanguageCode(): ?string;

    /**
     * @return string
     */
    public function getLanguageName(): ?string;
}
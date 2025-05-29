<?php

use App\Services\Translation\TranslationUnit;
use DateTime;
use InvalidArgumentException;

test('can create translation unit with valid data', function () {
    $sourceText = 'Hello World';
    $sourceLanguage = 'en';
    $targetLanguage = 'es';

    $translationUnit = new TranslationUnit($sourceText, $sourceLanguage, $targetLanguage);

    expect($translationUnit)
        ->toBeInstanceOf(TranslationUnit::class)
        ->and($translationUnit->getSourceText())->toBe($sourceText)
        ->and($translationUnit->getSourceLanguage())->toBe($sourceLanguage)
        ->and($translationUnit->getTargetLanguage())->toBe($targetLanguage)
        ->and($translationUnit->getTargetText())->toBeNull()
        ->and($translationUnit->getHistory())->toBeArray()->toBeEmpty()
        ->and($translationUnit->getId())->toBeString()->toStartWith('tu_')
        ->and($translationUnit->getCreatedAt())->toBeInstanceOf(DateTime::class)
        ->and($translationUnit->getUpdatedAt())->toBeInstanceOf(DateTime::class);
});

test('throws exception for invalid source language code', function () {
    $sourceText = 'Hello World';
    $sourceLanguage = 'english'; // Invalid language code
    $targetLanguage = 'es';

    expect(fn() => new TranslationUnit($sourceText, $sourceLanguage, $targetLanguage))
        ->toThrow(InvalidArgumentException::class, 'Language code must be a 2-letter ISO 639-1 code');
});

test('throws exception for invalid target language code', function () {
    $sourceText = 'Hello World';
    $sourceLanguage = 'en';
    $targetLanguage = 'spanish'; // Invalid language code

    expect(fn() => new TranslationUnit($sourceText, $sourceLanguage, $targetLanguage))
        ->toThrow(InvalidArgumentException::class, 'Language code must be a 2-letter ISO 639-1 code');
});

test('can update target text and track history', function () {
    $translationUnit = new TranslationUnit('Hello', 'en', 'es');
    
    // First update
    $firstTranslation = 'Hola';
    $translationUnit->updateTargetText($firstTranslation);
    
    expect($translationUnit->getTargetText())->toBe($firstTranslation)
        ->and($translationUnit->getHistory())->toBeEmpty(); // No history for first update

    // Second update
    $secondTranslation = '¡Hola!';
    $translationUnit->updateTargetText($secondTranslation);
    
    expect($translationUnit->getTargetText())->toBe($secondTranslation)
        ->and($translationUnit->getHistory())->toHaveCount(1)
        ->and($translationUnit->getHistory()[0])->toHaveKeys(['previousText', 'updatedAt'])
        ->and($translationUnit->getHistory()[0]['previousText'])->toBe($firstTranslation);
});

test('can get translation history with multiple updates', function () {
    $translationUnit = new TranslationUnit('Hello', 'en', 'es');
    
    $translations = [
        'Hola',
        '¡Hola!',
        '¡Hola Mundo!'
    ];

    foreach ($translations as $translation) {
        $translationUnit->updateTargetText($translation);
    }

    expect($translationUnit->getTargetText())->toBe($translations[2])
        ->and($translationUnit->getHistory())->toHaveCount(2)
        ->and($translationUnit->getHistory()[0]['previousText'])->toBe($translations[0])
        ->and($translationUnit->getHistory()[1]['previousText'])->toBe($translations[1]);
});

test('generates unique ids for different translation units', function () {
    $unit1 = new TranslationUnit('Hello', 'en', 'es');
    $unit2 = new TranslationUnit('World', 'en', 'es');

    expect($unit1->getId())->not->toBe($unit2->getId());
});

test('updates timestamp when target text is modified', function () {
    $translationUnit = new TranslationUnit('Hello', 'en', 'es');
    $initialUpdatedAt = $translationUnit->getUpdatedAt();
    
    // Wait for a moment to ensure timestamp difference
    sleep(1);
    
    $translationUnit->updateTargetText('Hola');
    
    expect($translationUnit->getUpdatedAt()->getTimestamp())
        ->toBeGreaterThan($initialUpdatedAt->getTimestamp());
});

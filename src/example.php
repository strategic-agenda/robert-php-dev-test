<?php

declare(strict_types=1);

require_once 'TranslationUnit.php';

// create a new instance of the TranslationUnit class
$translationUnit = new TranslationUnit(new PDO('mysql:host=localhost;dbname=robert_dev', 'root', 'root'));

// add new translation units
$unit1Id = $translationUnit->add('Hello world', ['fr' => 'Bonjour le monde', 'es' => 'Hola mundo']);
$unit2Id = $translationUnit->add('Welcome to Robert', ['fr' => 'Bienvenue à Robert']);

// retrieve a unit
$unit1 = $translationUnit->get($unit1Id);

// update the source and translations
$translationUnit->update(
    id: $unit2Id,
    sourceText: "Welcome to Robert CAT Tool",
    translations: ['fr' => 'Bienvenue à l\'outil Robert CAT', 'es' => 'Bienvenido al herramienta CAT de Robert']
);

// check the history
$history = $translationUnit->getHistory($unit1Id);

// get all units
$allUnits = $translationUnit->getAll();

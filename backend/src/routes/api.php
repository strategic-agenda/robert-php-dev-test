<?php

use Kernel\Http\Router;
use Intobi\Controller\Language\Save as LanguageSave;
use Intobi\Controller\LanguageList;
use Intobi\Controller\Translate\Save;
use Intobi\Controller\TranslateList;
use Intobi\Controller\Translator;

Router::get('/', LanguageList::class);

Router::get('/language', LanguageList::class);
Router::post('/language/save', LanguageSave::class);

Router::get('/translate', TranslateList::class);
Router::post('/translate/save', Save::class);

Router::get('/translator', Translator::class);

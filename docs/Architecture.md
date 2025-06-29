# Explaining the architecture and design decisions

A CAT application is fundamentally based on a series of sequencial processes that an input text undergoes to produce an output text.

-----------------------------------------------------------------------------------------------------------------------------------------

## 1. The CORE BASIC approach

```INPUT TEXT``` -> ```PROCESS STEP 1``` --> ```PROCESS STEP 2``` --> ```PROCESS STEP 3``` -> ```OUPUT TEXT```

Summed up, we can visualize the main core functionality of a CAT application by this:

```
INPUT TEXT -->
    -> Detect language ( dinamically or manually )
    -> Normalize text ( clean whitespaces, normalize characters etc)
    -> Chunking ( split long texts into chunks )
    -> Segment source ( rule based/models based or custom project constraints )
    -> Translation memory look up ( exact match, fuzzy search, neural)
    -> Store translation unit + version
    --> another step
    --> another step
    --> ...
    -> output generation
--> OUPUT TEXT
```

We can see that we are dealing with an design pattern based on sequencial steps in which each next step gets the output
of the previous step and continues processing. And one particular design approach is the **Pipeline Design Pattern**.

Now, each processing step can employ a different strategy, for example, language detection can be done manually, provided by the user, or
dynamically, where one service should be easily swappable with another service ( Custom Approach, FastText, Google API, CLD3).

We are now dealing with the **Strategy Design Pattern**, which allows us to easily swap one functionality with another without breakting
the overall system functionality.

Going further, given that when using services ( be them external or locally installed ) each service comes with its own API, we need to
adapted each service's API to the CAT system we are creating, so right away we are thinking about the ***Adapater Design Pattern***. 

Now, we are dealing with a bunch of working units, some small, some larger, and we need a way to manage them, to create them, inject them wherever and whenever we need them, and for this we can use the **Dependency Container Design Pattern** with the help of **Inversion of Control Design Pattern**
provided to us by the **Reflection API** in PHP.

For the database interaction, assuming a PostgreSQL/MSQL database, the **Repository Design Pattern** can be used, decoupling the database layer interaction from the application itself.

Many more other design patterns can be employed and expanded on the basic core approach, which provides a modular ( one can easily swap one functionality with another ), scalable ( one can increase decrease processing steps ) and flexible foundational base.

But what about the main software development philosopy? We have the basic core of our CAT application, but we need a way to structure and organize this, and one of the most popular and powerful approach is **DDD ( Domain Driven Design )**, which expands on the idea of scalability, modularity and flexibility.

```php
<?php
// SIMPLE CODE EXAMPLE OF THE CAT PIPELINE PROCESS

// set up the piple line steps services in a simple dependency container
$container->register('translationPipelinelanguageDetector', function() {
    return new FastTextDetector();
});

$container->register('translationPipelineSegmenter', function() {
    return new RuleBasedSegmenter();
});

$container->register('translationPipelineSegmenter', function() {
    return new RuleBasedSegmenter();
});


$container->register('databaseMemoryTranslationLookup', function() {
    return new DatabaseMemoryTranslationLookup();
});

// create pipeline and add steps
$pipeline = new TranslationPipeline();
$pipeline->addStep(new LanguageDetectionStep($container->get('translationPipelinelanguageDetector')));
$pipeline->addStep(new TextNormalizationStep());
$segmentationStep = new SegmentationStep($container->get('translationPipelineSegmenter'));
$pipeline->addStep($segmentationStep);
$databaseMemoryTranslationStep = new DatabaseMemoryTranslationStep($container->get('databaseMemoryTranslationLookup'));
$pipeline->addStep($databaseMemoryTranslationStep);

// prepare input text
$inputText = "Hello world! How are you today? This is a test.";

// proccess input text through the pipeline and return output text
$outputText = $pipeline->process($inputText);
```


## 2. Asyncron/Syncron Architectural approach
Some of the processing steps are resource intensive and can last longer, to have a even more flexible and scalable system, one can employ an **Event Driven Architecture** or microservices with message brokers like Kafka, RabbitMQ, AWS etc. Such a system it's more complex, difficult to manage while at the same time providing a truly powerful system where each step can be scaled independently by adding more workers, more processing power.


##  3. Multilingual content
Now, we have not really touched multilingual content, one one hand, one hand one can have an input text which contains content in multiple languages, in such a case, the language detection mechanism must be be able to block in the different languages in order to apply the correct segmentation process based on the block scope language. One the other hand, a translation unit an have multiple translations for any given language, the database should efficiently load the proper match.

## 4. Version control
Given that there can be a lot written about versioning, and given the scope of this example, I opted for a simple system, where using a table I keep track of all the changes made to a translation unit using a version identifier.

-----------------------------------------------------------------------------------------------------------------------------------------

## Running the Appplication
It uses docker to make our lives much easier, so in the root of the project, in the terminal run:
1. ```docker compose build```
2. ```docker compose up -d```
3. Exec into docker php container: ```docker exec -it cat_php_fpm``` and run ```composer install```
4. Now, in the frontend folder, ```npm install``` and ```npm run dev```, and visit: http://localhost:5173
5. In order to run the phpunit tests, exec back into that php container and run ```./vendor/bin/phpunit```.
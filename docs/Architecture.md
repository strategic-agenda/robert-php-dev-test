
# Architecture and Design Decisions

  

## Structure

The PHP application is using a modular structure, by structuring our application this way, we have the advantage of having a good level of abstraction making maintaining, updating and debuggin the codebase very easy.

  

### Modules

Modules provide a level of abstraction on our codebase making it much easier to develop, maintain and update. A Module can be a whole folder or even a single .php file.

  

The main modules used are: `logic`, `models`

  

Inside the `models` module we will find the Database.php file which handles the database connection and interactions

  

The `models` module also contains models for all the classes used in the `logic` module, this way we can provide an interface for the development of the logic part without having to deal with the database at all, making it much more easier, secure and reliable, avoiding spaghetti code and unsafe database queries.

  

A model file is represented with `m_` as a prefix.

  

### Database interactions extension

For the Database.php file inside the `models` module, we have decided to use PDO, a PHP extension which provides a very easy and reliable interface for accessing various database types.

  

## Design Patterns

We have decided to keep functions and classes as small as possible avoiding having any modules,files,classes or functions be way to long making them hard to understand and maintain.

  

For function naming we have decided to use **CamelCase**.

For variable and paremeter naming the developer must choose a name that suits the needs of the function making each variable easy to understand.

We suggest **camelCase** or `_` to seperate different words, but again we advice each developer to use a name that describes the variable well and it is easy to read.

  

Comments must be used while developing but within a reasonable extent, if something is obvious a comment is not needed.

## Translation Unit Version Control Implementation
  With our current implementation of the app, we keep track of changes made to the Translation Units in a seperate table named `translation_unit_records`. There we store the translated text, the version and the creation timestamp. At the same time each Translation Unit stores the number of its current version inside a version column in the database. By having this structure it is very easy to add an endpoint for retrieval of a previous version of a Translation Unit and go back and forth as many times as we wish without having any issues.
 But, there are some concerns we must raise at this point that need further discussion with the managment team to fine-tune our solution. For example, can you go back and forth infinite times between 2 versions? When you restore a previeous version, should the current one be archived or not? Those answers will define some additions or changes that might be needed for this feature to work as intented.
If we want to be able to move between 2 versions back and forth infinite times and we want when a previeous version of a unit gets restored the current version to be archived, then we must check if the unit's translated text is matching a previous version before inserting it to the archive to avoid having duplicates.




# Documentation

  

## Module `api` 

  

### File `translations.php`
Handles the requests for CRUD operations of the `TranslationUnit` class

### Endpoints:

### `api/Add`

 - Method : POST
 - Description : Adds a new Translation Unit
 -  Returns : New Translation Unit ID
 - Errors : HTTP 400, HTTP 500

 #### Args
- string text | Translation Unit text
-  int langId | Language Id (default points to English)
- string trans_text | The Translated Unit text

### `api/Update`

 - Method : POST
 - Description : Updates an existing Translation Unit
 -  Returns : HTTP 201
 - Errors : HTTP 400, HTTP 500

#### Args
- int id | Translation Unit id
- string trans_text | The Translated Unit text

### `api/Delete`

 - Method : POST
 - Description : Deletes an existing Translation Unit and its older versions
 -  Returns : HTTP 201
 - Errors : HTTP 400, HTTP 500

#### Args
- int id | Translation Unit id
 
 ### `api/Get`

 - Method : GET
 - Description : Get a translation unit or all of them
 -  Returns : A JSON containing an array which contains the objects requested
 - Errors : HTTP 400, HTTP 500

#### Args
- int id | Translation Unit Id or -1 to retrieve all units

## Module `logic` 

  

### File `TranslationUnit.php`

  

Contains the class `TranslationUnit` which handles actions requested by the api. Uses the model for the `TranslationUnit` to update the database. Any logic for the `TranslationUnit` will be added inside this class


  
 ### Function `AddTranslationUnit(string text , int langId , string trans_text)`

 - Description : Handles any logic for the add of a new Translation Unit
 -  Returns : New Translation Unit ID

#### Args
- string text | Translation Unit text
- int langId   | Language Id
- string trans_text | The Translated Unit text  

 ### Function `UpdateTranslationUnit(int id , string trans_text)`

 - Description : Handles any logic for the update of an existing Translation Unit
 -  Returns :  A bool stating the success of the operation

#### Args
- int id | Translation Unit Id
- string trans_text | The Translated Unit text

 ### Function `DeleteTranslationUnit(int id)`

 - Description : Handles any logic for the deletion of an existing Translation Unit and its older versions
 -  Returns :  A bool stating the success of the operation

#### Args
- int id | Translation Unit Id

 ### Function `GetTranslationUnit(int id)`

 - Description : Handles any logic for the retrieval a translation unit
 -  Returns : An array which contains the object requested

#### Args
- int id | Translation Unit Id

 ### Function `GetAllTranslationUnits()`

 - Description : Handles any logic for the retrieval of all translation units
 -  Returns : An array which contains the objects requested

## Module `models`

  

### File `Database.php`

  

Contains the class `DatabaseConnection` which opens a database connection. Contains functions tha allow usage of the `DatabaseConnection` class by other modules

### Class `DatabaseConnection`

 - Description : Creates and handles a new database connection

#### Methods
-  `Connect()` | Creates a connection
- `GetConnection ()` | Retrives the existing connection

### Function`InitializeDB`

 - Description : Initializes the `DatabaseConnection` **class** and creates a connection
 - Returns : True
  
### Function`GetConnection`

 - Description : Creates and handles a new database connection
 - Returns : The Connection handle (type PDO)

---

### File `m_TranslationUnit.php`

  

Contains the class `TranslationUnitModel` which is the model class for `TranslationUnit`, handles all database operations needed by the `TranslationUnit` class
  
### Function `AddTranslationUnit(string text , int langId , string trans_text)`

 - Description : Creates a new translation unit in the database
 - Returns : New Translation Unit ID
 
#### Args
-  string text   |  Translation Unit text
-  int langId   |  Language Id   
- string trans_text   |  The Translated Unit text 
- 
### Function `UpdateTranslationUnit(int id , string trans_text)`

 - Description : Updates a translation unit's values in the database and creates a unit version record
 - Returns : New Translation Unit ID
 
#### Args
-  int id |  Translation Unit Id
- string trans_text   |  The Translated Unit text 

### Function `DeleteTranslationUnit(int id)`

 - Description : Deletes a translation unit and all its previews versions from the database
 - Returns :  A bool stating the success of the operation
 
#### Args
-  int id |  Translation Unit Id

 ### Function `GetTranslationUnit(int id)`

 - Description : Retrives a translation unit from the database
 - Returns :  An array which contains the object requested
 
#### Args
-  int id |  Translation Unit Id

  ### Function `GetAllTranslationUnits()`

 - Description : Retrives a translation unit from the database
 - Returns : An array which contains the objects requested
 
### Other methods

There are some other methods that exist in the model for further interactions with the database but they are not used and not tested yet so we will not include them in the documentation, those methods are the following

#### Function `AddTranslationUnitRecord`

Used for further abstraction inside the UpdateTranslationUnit method which is not needed at this point

#### Function `GetTranslationUnitRecordByUnIDandVersion`

Retrieves translation unit record by unit id and version

#### Function `GetAllTranslationUnitRecords`

Retrieves all translation unit records

#### Function `DeleteTranslationUnitRecordByUnIDandVersion`

Deletes translation unit record by unit id and version

#### Function `DeleteTranslationUnitRecord`

Deletes translation unit record

#### Function `DeleteTranslationUnitRecords`

Deletes all translation unit records

  

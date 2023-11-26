# Explaining the architecture and design decisions
The architecture was implemented in the following way:
1) Renamed folder API to the backend. Because the backend is more understandable what happens inside.
2) A new folder for the frontend where all files are placed, which is related to the frontend part.
3) Tests also moved into the backend folder. Because we can write also frontend tests in the future. So I decided to move tests into the backend folder.

For connection to the database I used the singleton pattern to connect to the database.
We can find it inside kernel/DB/Connection.php.

Also according to the evolution of the application, we can decide what should be implemented now to make the development and extensions more simple and clean and avoid any troubles with the testing and maintaining.


Inside of backend, I split the code into two parts backend code and kernel code.
The backend code is responsible for API calls inside of the src folder. Here we can find controllers, models, and routers.
The kernel code is responsible for core functionality. Like the base controller, db connections, loaders, etc...

Also, I decided to split the configs file into a separate directory where we can define the configs that we need to set up the project as we want.

Inside the src/Controller folder I decided to Split controllers according to the functionality of page/action
Inside the src/Model folder I organize the models according to the functionality and decided to add Interface for the LanguageModel.php class, for future DI implementation.

As a reference and I choose the Laravel framework structure (but not  at all)  and integrate the Symfony component for getting the http parameters.

For the Frontend part I chose the simple React application without any frameworks like Next.js or something like that.
But I prefer to use Next.js instead of vanilla React application.

I decided to make the simple and clean frontend of application without hard elements.
All pages created with minimalism but with the functional elements. The functionality on the each page enough to understand how to work with it.


# Deploy locally
* docker stop $(docker ps -aq)
* docker-compose up -d
* docker exec -ti PHP-FPM-CONTAINER bash
* composer install (<ins>In php-fpm container</ins>)
* run sql script from database/schema.sql file
* http://localhost:3000/ - frontend
* http://localhost:8080/ - backend
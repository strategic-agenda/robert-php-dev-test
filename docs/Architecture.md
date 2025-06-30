# Application Documentation

## About the app
A fast and scalable translation app that leverages MySQL for storing user data, translation history, and language pairs, while utilizing Redis for caching frequently requested translations to significantly improve response times and reduce database load. This architecture ensures both data reliability and high performance, making real-time multilingual communication seamless.

This document provides a general overview of application setup process. The following sections outline the steps must be taken.

### 1. Setup

a. Installation of docker - https://www.docker.com/get-started/<br />
b. Clone the repo and checkout to the relevant branch<br />
c. Open the repo folder using terminal ( Linux or Mac required )<br />
d. Add execution right to "start-local.sh" file.<br />
e. Run "start-local.sh" in terminal using "./start-local.sh" command.

### 2. Installation of database
a. Navigate to http://localhost:3022/ in a browser and login to phpMyAdmin using "root" as username "1234" as password.<br />
b. Import the file "interview.sql" using import button in phpMyAdmin. Installation will provide required translations for app to run.<br />

### 3. App usage
After the successful setup, nginx will provide local domains for front-end backend. Front-end will be reachable by navigating to "http://www.interview.localhost/".



# Laravel 12.0 Boilerplate | Laravel Passport

This is a Laravel 12 API project that that I have developed for attribute value extraction functionality :

- Kindly go though complete document


- Core Models & Relations:
    - **User:** first_name, last_name, email, password
    - **Project:** name, status
    - **Timesheet:** task_name, date, hours
    - Many-to-many relation between Users and Projects; one-to-many for Timesheets.
- Dynamic Attributes (EAV) for Projects:
    - **Attribute:** name, type (text, date, number, select)
    - **AttributeValue:** attribute_id, entity_id, value
- API Endpoints with Laravel Passport for authentication.
- Flexible filtering on both regular and dynamic EAV attributes.
- Pagination and ordering for listing endpoints.

## Setup Instructions

1. **Clone the Repository**
   ```
   git@github.com:srafiqdotcom/Laravel-Passport-Boilerplate.git
   cd Laravel-Passport-Boilerplate


### Install Dependencies


    composer install

### Environment Setup


Copy the example environment file:


    cp .env.example .env

Update your .env file with your database credentials and other configurations.

Generate an application key:

    php artisan key:generate

### Database Setup

Run migrations:

    php artisan migrate
Seed the database:

    php artisan db:seed

Install Laravel Passport

    php artisan passport:install

Serve the Application

    php artisan serve
The API will be available at http://127.0.0.1:8000.



## API Documentation

### Authentication

#### Register: POST /api/register

Example Body:

    {
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "password": "secret123"
    }
Response: Returns the user and an access token.

####  Login: POST /api/login

Example Body:

    {
    "email": "john@example.com",
    "password": "secret123"
    }
Response: Returns the user and an access token.

####  Logout: POST /api/logout

    Headers:
    Authorization: Bearer <access_token>


### Users

#### List Users: GET /api/users

#### Show User: GET /api/users/{id}
Example Curl

        curl --request GET \
        --url http://127.0.0.1:8000/api/users \
        --header 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9' \
        --header 'Content-Type: application/json' \
        --data '{
            "order_by": "id",
            "order": "desc"
        }'

#### Create User: POST /api/users
Example Curl

        curl --request POST \
        --url http://127.0.0.1:8000/api/users \
        --header 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9' \
        --header 'Content-Type: application/json' \
        --data '{
            "first_name": "Alice",
            "last_name": "Smith",
            "email": "aliceundo1@gmail.com",
            "password": "admin123"
        }'

#### Update User: PUT /api/users/{id}
Example Curl

        curl --request PUT \
        --url http://127.0.0.1:8000/api/users/1 \
        --header 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9' \
        --header 'Content-Type: application/json' \
        --data '{
            "first_name": "alicede",
            "last_name": "leww",
            "email": "aliceundo+1@gmail.com",
            "password": "admin123"
        }'

#### Delete User: DELETE /api/users/{id}
Example Curl

        curl --request DELETE \
        --url http://127.0.0.1:8000/api/users/5 \
        --header 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9'

### Projects

#### List Projects: GET /api/projects

Example Curl

        curl --request GET \
        --url http://127.0.0.1:8000/api/projects \
        --header 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9'

Filtering Examples:

    GET /api/projects?filters[name]=ProjectA
    GET /api/projects?filters[department]=IT
    GET /api/projects?filters[name][like]=%Alpha%&order_by=name&order=desc

#### Show Project: GET /api/projects/{id}
Example Curl

    curl --request GET \
    --url http://127.0.0.1:8000/api/projects/3 \
    --header 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9'


#### Create Project: POST /api/projects
Curl Example

    curl --request POST \
    --url http://127.0.0.1:8000/api/projects \
    --header 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9' \
    --header 'Content-Type: application/json' \
    --data '{
    "name": "Project charlie",
    "status": "active",
    "attributes": {
        "department": "IT",
        "start_date": "2025-01-01",
        "end_date": "2025-12-31",
        "budget": "50000"
    }
    }'


#### Update Project: PUT /api/projects/{id}
Curl Example

        curl --request PUT \
    --url http://127.0.0.1:8000/api/projects/3 \
    --header 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9' \
    --header 'Content-Type: application/json' \
    --data '{
    "name": "Project Alpha update",
    "status": "active"
    }'


#### Delete Project: DELETE /api/projects/{id}

Curl Example

    curl --request DELETE \
    --url http://127.0.0.1:8000/api/projects/5 \
    --header 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9'



### Timesheets

#### List Timesheets: GET /api/timesheets
Example Curl

    curl --request GET \
    --url http://127.0.0.1:8000/api/timesheets \
    --header 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9'



#### Show Timesheet: GET /api/timesheets/{id}
Example Curl

        curl --request GET \
        --url http://127.0.0.1:8000/api/timesheets/1 \
        --header 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9'


#### Create Timesheet: POST /api/timesheets
Example Curl

        curl --request POST \
        --url http://127.0.0.1:8000/api/timesheets \
        --header 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9' \
        --header 'Content-Type: application/json' \
        --data '{
        "task_name": "Develop A stuidio API endpoints",
        "date": "2025-05-01",
        "hours": 8,
            "user_id": 4,
                "project_id": 4

        }'


#### Update Timesheet: PUT /api/timesheets/{id}
Example Curl

        curl --request PUT \
        --url http://127.0.0.1:8000/api/timesheets/1 \
        --header 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9' \
        --header 'Content-Type: application/json' \
        --data '{
            "first_name": "alicede",
            "last_name": "leww",
            "email": "aliceundo+1@gmail.com",
            "password": "admin123"
        }'

#### Delete Timesheet: DELETE /api/timesheets/{id}

    curl --request DELETE \
    --url http://127.0.0.1:8000/api/timesheets/2 \
    --header 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9'



Test Credentials

Use these credentials for testing if you have imported the dump I have provided

Email: shahid@studio.ae
Password: admin123

if you use seeders

Then use the email from table
password secret123

# Addition Things

#### Repository Design Pattern
#### Custom Exception handling in handler.php
#### Multilingual Base
#### Custom code in each response so that if set each response/exception can be found out
#### separate logging for each module
#### Scribe Documentations
    php artisan scribe:generate 
After setting up this project, if you run above command, you will see whole documentation in detail for each endpoint. /docs url.



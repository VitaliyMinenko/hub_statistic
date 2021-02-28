# Github statistic.
#### Version 1.0b
#### Author: Vitalii Minenko

A simple application which will help us to compere two different GitHub repositories, and check which repository is more popular.


### Requirements
For correct work should install PHP with minimum version 5.4.0

### Haw we can start the application.
* Download this application from repository.
* Open shell terminal and go to folder with application.
* Start next command.

```
php artisan serve
``` 
* After you can see next message.

```
Laravel development server started on http://127.0.0.1:8000/
``` 

* Now application is ready you can use it with API interfaces:
* For test application you can use any Rest Clients for example (Postman, Advanced Rest Client etc...).

##### Method of HTTP Request.

* POST

##### Headers of HTTP Request.
* Content-Type : application/json

##### Api commands and example of answers.

* Example request for comparing github repositories. (Url)

```
http://localhost:8000/api/get-information-by-repositories/
```

##### Api interface can accept only the following parameters.
* first_repository - Repository name in next format Author/Repository `auxiliary/rater`.
* second_repository - Repository name in next format Author/Repository `retejs/rete`.

```
{
  "first_repository": "auxiliary/rater",
  "second_repository": "retejs/rete/"
}
```

Example of answear by our request. 

```
{
    "status": "ok",
    "message": "",
    "response": {
        "compered info": {
        "Best result by forks": "retejs/rete - 401",
        "Best result by stars": "retejs/rete - 6380",
        "Best result by watchers": "retejs/rete - 6380",
        "Most new": "Undefined",
        "Haw many PR is open": "retejs/rete - 3",
        "Haw many PR is closed": "The value is same"
    },
    "first repository info": {
    "auxiliary/rater": {
    "Number of forks": 43,
    "Number of stars": 112,
    "Number of watchers": 112,
    "Date of the latest release": "Date is undefined",
        "Pull requests": {
        "open": 1,
        "close": 0
        }
    }
 },
    "second repository info": {
        "retejs/rete": {
        "Number of forks": 401,
        "Number of stars": 6380,
        "Number of watchers": 6380,
        "Date of the latest release": "2020-10-17 18:34",
            "Pull requests": {
            "open": 3,
            "close": 0
            }
        }
    }
 }   
}
```
Example answear if repository is not exist.
```
{
    "status": "ok",
    "message": " By second repository name nothing found.",
    "response": [],
}
```



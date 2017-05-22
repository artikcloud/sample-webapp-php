# ARTIK Cloud:  PHP Webapp Starter 

This is a sample PHP webapp starter using ARTIK Cloud and setting up the SDK.   The sample will Authenticate to ARTIK Cloud and use the Messages API to Send and Retrieve a message to a device.  

View this live sample here:
https://radiant-anchorage-11263.herokuapp.com/

## Requirements

The sample uses php symfony webapp framework and apache web server.

- [ARTIK Cloud SDK for PHP](https://github.com/artikcloud/artikcloud-php) (version 2.0.8 or greater)
- [PHP](http://php.net/manual/en/install.php)  (Version >= 5.5.0)
- [Composer - Installation](https://getcomposer.org/)

## Introduction

The tutorial [Your first Web app](https://developer.artik.cloud/documentation/tutorials/your-first-application.html) at [https://developer.artik.cloud/documentation](https://developer.artik.cloud/documentation) describes what the system does and how it is implemented.

Consult [Initial setup](https://developer.artik.cloud/documentation/tutorials/your-first-application.html#initial-setup) in the tutorial to set up the cloud and web app.

## Setup

1. Be sure you have installed all the requirements.
2. Follow the Initial Setup Guide from the Introduction section above to setup your cloud application, webapp, and devices.

**Create a php webapp**:

This project uses symfony-standard web framework.   You can learn more about this [here](https://github.com/symfony/symfony-standard).

Git clone the **symfony-standard** edition:

```
git clone https://github.com/symfony/symfony-standard
```

Now copy all files from our php sample to the above project.   Here is a list of the files:

```
composer.json
app/Resources/views/default/index.html.twig
app/config/config_prod.yml
src/AppBundle/Controller/DefaultController.php
src/AppBundle/Helper/Helper.php
```

**Run Project**:

In your project, run composer so it installs dependencies for the project:

 ```
composer install
 ```

Setup Apache to serve your project at the /web directory and on port 8000.

**Alternatively**, you can run with PHP built-in webserver:

```
cd your_project_name/
composer install
php bin/console --env=prod cache:clear
php bin/console server:run
```

Project will run by default at the following location:  http://localhost:8000/.  

## More about ARTIK Cloud

If you are not familiar with ARTIK Cloud, we have extensive documentation at [https://developer.artik.cloud/documentation](https://developer.artik.cloud/documentation)

The full ARTIK Cloud API specification can be found at [https://developer.artik.cloud/documentation/api-spec.html](https://developer.artik.cloud/documentation/api-spec.html)

Peek into advanced sample applications at [https://developer.artik.cloud/documentation/samples/](https://developer.artik.cloud/documentation/samples/)

To create and manage your services and devices on ARTIK Cloud, visit the Developer Dashboard at [https://developer.artik.cloud](https://developer.artik.cloud/)

## License and Copyright

Licensed under the Apache License. See LICENSE.

Copyright (c) 2017 Samsung Electronics Co., Ltd.](https://)

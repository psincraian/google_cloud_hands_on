# 🚀 Deploy to Google Cloud App Engine

## Create Google Cloud application
Firstly we need to create a project in [Google Cloud App Engine](https://console.cloud.google.com/projectselector/appengine/create?lang=flex_php&st=true) with billing enabled.

## Connect SDK with Google Cloud
To connect you locally Google Cloud SDK simply run

```
gcloud auth login
```

## Deploy
Given the `app` same as the previous step now we will deploy to Google Cloud
App Engine.

Firstly create an `app.yaml` file with the following data:

```yaml
runtime: php
env: flex

runtime_config:
  document_root: public

env_variables:
  APP_ENV: dev
  APP_SECRET: 0ca71dea3918bfac138b5ba990036d27

```

This file tells App Engine all about our service, like the programming language,
what to execute, and so on. You can configure the number of instances that will
handle the requests, the memory of the instances and so on.

Then execute `gcloud app deploy` to deploy the app to App Engine. This will
take some time.

Finally execute `gcloud app browse` to view your application in the browser.

# 🛠️ Creating a Hello World App
In this section we will create a Hello World app. We will use Symfony 4 and it
already has a command to create a basic application skeleton.

## Create skeleton
To create a Hello World app run:
```cli
composer create-project symfony/skeleton app
```

## Add a Hello World controller
Add a default controller which simply returns a text message. Create the
`DefaultController.php` file in `src/Controller/` with the following code:

```php
<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @return Response
     */
    public function index()
    {
        return new Response('With ❤ from Barcelona!');
    }
}
```

Add the default route:

```yaml
# config/routes.yaml
index:
    path: /
    controller: App\Controller\DefaultController::index
```

And then execute the following line to start the server:
```cli
php -S 127.0.0.1:8000 -t public
```

# 🔗 Connect to Google Cloud SQL
In this step we will add a PostgreSQL database to our best app.

# Create PostgreSQL instance
Firstly we need to enable the Cloud SQL API [link](https://console.cloud.google.com/flows/enableapi?apiid=sqladmin&redirect=https:%2F%2Fconsole.cloud.google.com).

After that we can create a Cloud SQL instance with the following command:
```
gcloud sql instances create db --tier db-f1-micro --database-version=POSTGRES_9_6 --region=europe-west2
```

This command will create a PostgreSQL instance with the lowest machine
(db-f1-micro), which has shared vCPU and 0.6 of RAM.

Now we will set a password to the default user:
```
gcloud sql users set-password postgres no-host --instance db --password petru242
```

Create a database:
```
gcloud sql databases create handson --instance=db
```

Record the connection name:
```
gcloud sql instances describe db | grep connectionName
```

The connection name should be like `connectionName: poetic-pottery-198108:europe-west2:db`

# Connect Symfony with DB

Open `app.yaml` file and add the following setting replacing `CONNECTION_NAME` placeholder by the recorded one:
```yaml
beta_settings:
    cloud_sql_instances: CONNECTION_NAME
```

Add doctrine to composer:
```
composer install doctrine
```

Change doctrine config to:
```
# config/packaged/doctrine.yaml
doctrine:
    dbal:
        charset: utf8
        driver: pdo_pgsql
        host: /cloudsql/CONNECTION_NAME
        port: 5432
        dbname: handson
        user: postgres
        password: petru242
```

And finally we will change our `DefaultController` to add a bit of logic. The page will show all the IP that visited our page and time stamp when happened.

The code is the following:

```php
<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\DBAL\Connection;

class DefaultController extends Controller
{
    /**
     * @return Response
     */
    public function index(Request $request)
    {
        $conn = $this->get('doctrine.dbal.default_connection');
        $this->createTable($conn);
        $userIp = $this->getIp($request);
        $this->insert($conn, $userIp);
        $visits = $this->getLast10($conn);

        return new Response(implode("\n", $visits), 200, ['Content-Type' => 'text/plain']);
    }

    private function createTable(Connection $conn)
    {
        $conn->query('CREATE TABLE IF NOT EXISTS visits ' .
        '(time_stamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP, user_ip CHAR(64))');
    }

    private function getIp(Request $request)
    {
        $ip = $request->GetClientIp();
       // Keep only the first two octets of the IP address.
       $octets = explode($separator = ':', $ip);
       if (count($octets) < 2) {  // Must be ip4 address
           $octets = explode($separator = '.', $ip);
       }
       if (count($octets) < 2) {
           $octets = ['bad', 'ip'];  // IP address will be recorded as bad.ip.
       }
       $octets = array_map(function ($x) {
           return $x == '' ? '0' : $x;
       }, $octets);

       return $octets[0] . $separator . $octets[1];
    }

    private function insert(Connection $conn, string $userIp)
    {
        $insert = $conn->prepare('INSERT INTO visits (user_ip) values (:user_ip)');
        $insert->execute(['user_ip' => $userIp]);
    }

    private function getLast10(Connection $conn)
    {
        $select = $conn->prepare(
        'SELECT * FROM visits ORDER BY time_stamp DESC LIMIT 10');
        $select->execute();
        $visits = ["Last 10 visits:"];
        while ($row = $select->fetch(\PDO::FETCH_ASSOC)) {
            array_push($visits, sprintf('Time: %s Addr: %s', $row['time_stamp'],
                $row['user_ip']));
        }
        return $visits;
    }
}

```

## Deploy the updated version
Now we can deploy a new version to our app:
```
gcloud app deploy
```
